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

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
}
