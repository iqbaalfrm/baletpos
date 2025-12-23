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
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SalesReturnResource extends Resource
{
    protected static ?string $model = SalesReturn::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
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
        ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListSalesReturns::route('/'),
            'create' => Pages\CreateSalesReturn::route('/create'),
            'edit' => Pages\EditSalesReturn::route('/{record}/edit'),
        ];
    }
}
