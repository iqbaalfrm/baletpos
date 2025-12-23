<?php

namespace App\Filament\Resources\SalesReturnResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SalesReturnDetail;

class DetailsRelationManager extends RelationManager
{
    protected static string $relationship = 'details';

    public function form(Form $form): Form
    {
        return $form
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
                    ->prefix('Rp')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('subtotal')
                    ->label('Subtotal')
                    ->prefix('Rp')
                    ->numeric()
                    ->readOnly(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('product.name')
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk'),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah'),

                Tables\Columns\TextColumn::make('refund_price')
                    ->label('Harga Refund')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money('IDR'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}