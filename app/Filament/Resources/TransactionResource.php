<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Product;
use App\Models\SalesReturn;
use App\Models\SalesReturnDetail;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Riwayat Transaksi';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('invoice_code')
                                    ->label('No. Invoice')
                                    ->readOnly(),

                                Forms\Components\DatePicker::make('created_at')
                                    ->label('Tanggal Transaksi')
                                    ->readOnly(),

                                Forms\Components\TextInput::make('user_name')
                                    ->label('Kasir')
                                    ->formatStateUsing(fn ($record) => $record->user->name ?? '-')
                                    ->readOnly(),
                                    
                                Forms\Components\TextInput::make('customer_name')
                                    ->label('Pelanggan')
                                    ->default('Umum')
                                    ->readOnly(),

                                Forms\Components\Select::make('payment_method')
                                    ->label('Metode Bayar')
                                    ->options([
                                        'cash' => 'Tunai',
                                        'qris' => 'QRIS',
                                        'transfer' => 'Transfer Bank',
                                    ])
                                    ->disabled(),

                                Forms\Components\Select::make('status')
                                    ->options([
                                        'completed' => 'Selesai',
                                        'void' => 'Dibatalkan (Void)',
                                    ])
                                    ->disabled(),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema([
                                Forms\Components\TextInput::make('total_amount')
                                    ->label('Total Belanja')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readOnly(),

                                Forms\Components\TextInput::make('payment_amount')
                                    ->label('Bayar')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readOnly(),

                                Forms\Components\TextInput::make('change_amount')
                                    ->label('Kembali')
                                    ->prefix('Rp')
                                    ->numeric()
                                    ->readOnly(),
                            ]),
                    ])
                    ->columnSpan(['lg' => 1]),

                Forms\Components\Section::make('Detail Barang')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->disabled(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Qty')
                                    ->disabled(),

                                Forms\Components\TextInput::make('selling_price_at_date')
                                    ->label('Harga Satuan')
                                    ->prefix('Rp')
                                    ->disabled(),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->prefix('Rp')
                                    ->disabled(),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false),
                    ])
                    ->columnSpanFull(),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_code')
                    ->label('Nota')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Bayar')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'void' => 'danger',
                        default => 'warning',
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'completed' => 'Selesai',
                        'void' => 'Void / Batal',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['created_from'], fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\Action::make('print')
                    ->label('Cetak')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Transaction $record) => route('print.struk', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('retur_barang')
                    ->label('Retur')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->visible(fn () => auth()->user()->isAdmin())
                    ->modalHeading('Retur Barang Transaksi Ini')
                    ->form(function (Transaction $record) {
                        return [
                            Forms\Components\DatePicker::make('date')
                                ->label('Tanggal Retur')
                                ->default(now())
                                ->required(),
                                
                            Forms\Components\Textarea::make('reason')
                                ->label('Alasan Retur')
                                ->required(),

                            Forms\Components\Repeater::make('return_items')
                                ->label('Pilih Barang yang Diretur')
                                ->default($record->details->map(fn ($detail) => [
                                    'product_id' => $detail->product_id,
                                    'product_name' => $detail->product->name,
                                    'qty_available' => $detail->quantity, 
                                    'qty_return' => 0, 
                                    'price' => $detail->selling_price_at_date,
                                ])->toArray())
                                ->schema([
                                    Forms\Components\Hidden::make('product_id'),
                                    Forms\Components\Hidden::make('price'),
                                    
                                    Forms\Components\TextInput::make('product_name')
                                        ->label('Nama Produk')
                                        ->disabled(),

                                    Forms\Components\TextInput::make('qty_available')
                                        ->label('Qty Beli')
                                        ->disabled(),

                                    Forms\Components\TextInput::make('qty_return')
                                        ->label('Jml Retur')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->lte('qty_available') 
                                        ->required(),
                                ])
                                ->columns(3)
                                ->addable(false) 
                                ->deletable(false)
                                ->reorderable(false)
                        ];
                    })
                    ->action(function (array $data, Transaction $record) {
                        $itemsToReturn = collect($data['return_items'])->filter(fn ($item) => $item['qty_return'] > 0);

                        if ($itemsToReturn->isEmpty()) {
                            Notification::make()->title('Gagal')->body('Minimal 1 barang diretur.')->danger()->send();
                            return;
                        }

                        $totalRefund = $itemsToReturn->sum(fn ($item) => $item['qty_return'] * $item['price']);

                        $salesReturn = SalesReturn::create([
                            'transaction_id' => $record->id,
                            'user_id' => auth()->id(),
                            'date' => $data['date'],
                            'reason' => $data['reason'],
                            'total_refund' => $totalRefund,
                        ]);

                        foreach ($itemsToReturn as $item) {
                            SalesReturnDetail::create([
                                'sales_return_id' => $salesReturn->id,
                                'product_id' => $item['product_id'],
                                'quantity' => $item['qty_return'],
                                'refund_price' => $item['price'],
                                'subtotal' => $item['qty_return'] * $item['price'],
                            ]);

                            $product = Product::find($item['product_id']);
                            if ($product) {
                                $product->increment('stock', $item['qty_return']);
                            }
                        }

                        Notification::make()->title('Retur Berhasil')->success()->send();
                    }),

                Tables\Actions\Action::make('void_transaction')
                    ->label('Void')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan Transaksi (Void)')
                    ->modalDescription('Apakah anda yakin? Stok akan dikembalikan dan transaksi ditandai sebagai VOID.')
                    ->visible(fn (Transaction $record) => auth()->user()->isAdmin() && $record->status !== 'void')
                    ->action(function (Transaction $record) {
                        if ($record->status === 'void') return;

                        foreach ($record->details as $detail) {
                            $product = Product::find($detail->product_id);
                            if ($product) {
                                $product->increment('stock', $detail->quantity);
                            }
                        }

                        $record->update(['status' => 'void']);
                        
                        Notification::make()->title('Transaksi berhasil di-VOID')->success()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        // Finance user can only view, not edit transactions
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        // Finance user cannot delete transactions
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Cashier can only see transactions from their user ID
        if ($user->role === 'cashier') {
            return $query->where('user_id', Auth::id());
        }

        // Finance can view all transactions but can't edit/delete
        // Admin can see everything
        return $query;
    }
}