<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SalesReturnResource\Pages;
use App\Filament\Resources\SalesReturnResource\RelationManagers;
use App\Models\SalesReturn;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class SalesReturnResource extends Resource
{
    protected static ?string $model = SalesReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Retur Barang';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Retur')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal Retur')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('transaction_id')
                            ->label('No. Nota')
                            ->relationship('transaction', 'invoice_code')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Admin')
                            ->relationship('user', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->visible(auth()->user()->isAdmin()),

                        Forms\Components\Textarea::make('reason')
                            ->label('Alasan Retur')
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Detail Barang Retur')
                    ->schema([
                        Forms\Components\Repeater::make('details')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('product_id')
                                    ->label('Produk')
                                    ->relationship('product', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),

                                Forms\Components\TextInput::make('quantity')
                                    ->label('Jumlah')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\TextInput::make('refund_price')
                                    ->label('Harga Refund')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),

                                Forms\Components\TextInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->dehydrated(false),
                            ])
                            ->columns(4)
                            ->afterStateUpdated(function (array $state, Forms\Set $set) {
                                $total = collect($state)->sum('subtotal');
                                $set('total_refund', $total);
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\TextInput::make('total_refund')
                            ->label('Total Refund')
                            ->prefix('Rp')
                            ->readOnly()
                            ->numeric(),
                    ])
                    ->columnSpan(1),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->label('Tanggal'),

                Tables\Columns\TextColumn::make('transaction.invoice_code')
                    ->label('No. Nota')
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Admin'),

                Tables\Columns\TextColumn::make('reason')
                    ->label('Alasan'),

                Tables\Columns\TextColumn::make('total_refund')
                    ->money('IDR')
                    ->label('Total Refund'),

                // Tampilkan jumlah item yg diretur
                Tables\Columns\TextColumn::make('details_count')
                    ->counts('details')
                    ->label('Jml Item'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            RelationManagers\DetailsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesReturns::route('/'),
            'create' => Pages\CreateSalesReturn::route('/create'),
            'edit' => Pages\EditSalesReturn::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        $user = auth()->user();

        // Only admin can access sales returns, cashier cannot
        // Finance user can view sales returns
        if ($user->role === 'cashier') {
            return $query->where('id', 0); // Return empty set
        }

        return $query;
    }

    public static function canEdit($record): bool
    {
        // Finance user can only view, not edit sales returns
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }

    public static function canDelete($record): bool
    {
        // Finance user cannot delete sales returns
        $user = auth()->user();
        if ($user->role === 'finance') {
            return false;
        }
        return $user->role === 'admin';
    }
}