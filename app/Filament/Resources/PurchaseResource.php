<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PurchaseResource\Pages;
use App\Filament\Resources\PurchaseResource\RelationManagers;
use App\Models\Purchase;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Pembelian';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Informasi Pembelian')
                ->schema([
                    Forms\Components\Select::make('supplier_id')
                        ->relationship('supplier', 'name')
                        ->required()
                        ->label('Supplier'),
                    
                    Forms\Components\DatePicker::make('purchase_date')
                        ->default(now())
                        ->required()
                        ->label('Tanggal Beli'),

                    Forms\Components\Hidden::make('user_id')
                        ->default(auth()->id()),
                ]),

            Forms\Components\Section::make('Barang Masuk')
                ->schema([
                    Forms\Components\Repeater::make('details')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->relationship('product', 'name')
                                ->required()
                                ->label('Pilih Produk')
                                ->columnSpan(2),

                            Forms\Components\TextInput::make('quantity')
                                ->numeric()
                                ->default(1)
                                ->required()
                                ->reactive() 
                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                    $set('subtotal', $state * $get('unit_cost'))
                                ),

                            Forms\Components\TextInput::make('unit_cost')
                                ->label('Harga Beli Satuan')
                                ->numeric()
                                ->prefix('Rp')
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(fn ($state, Forms\Get $get, Forms\Set $set) => 
                                    $set('subtotal', $state * $get('quantity'))
                                ),

                            Forms\Components\TextInput::make('subtotal')
                                ->numeric()
                                ->prefix('Rp')
                                ->readOnly(),
                        ])
                        ->columns(5)
                        ->live()
                        ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                            // Hitung Total Otomatis
                            $details = $get('details');
                            $sum = 0;
                            foreach ($details as $detail) {
                                $sum += ($detail['subtotal'] ?? 0);
                            }
                            $set('total_amount', $sum);
                        }),
                ]),
                
            Forms\Components\TextInput::make('total_amount')
                ->label('Total Pembelian')
                ->numeric()
                ->prefix('Rp')
                ->readOnly(),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal Beli')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pembeli')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Pembelian')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('purchase_from')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('purchase_until')->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['purchase_from'], fn (Builder $query, $date) => $query->whereDate('purchase_date', '>=', $date))
                            ->when($data['purchase_until'], fn (Builder $query, $date) => $query->whereDate('purchase_date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'edit' => Pages\EditPurchase::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Cashier cannot see purchase records (admin & finance can view)
        if ($user->role === 'cashier') {
            return $query->where('id', 0); // Return empty set
        }

        return $query;
    }

    public static function canEdit($record): bool
    {
        // Finance user can only view, not edit purchases
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        // Finance user cannot delete purchases
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }
}
