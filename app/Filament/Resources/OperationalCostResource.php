<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperationalCostResource\Pages;
use App\Models\OperationalCost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OperationalCostResource extends Resource
{
    protected static ?string $model = OperationalCost::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Biaya Operasional';

    protected static ?string $pluralLabel = 'Biaya Operasional';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->required()
                            ->default(now()),
                        
                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'listrik' => 'Listrik',
                                'air' => 'Air',
                                'gaji' => 'Gaji',
                                'atk' => 'ATK',
                                'perlengkapan' => 'Perlengkapan',
                                'pemeliharaan' => 'Pemeliharaan',
                                'transportasi' => 'Transportasi',
                                'lainnya' => 'Lainnya',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('description')
                            ->label('Deskripsi')
                            ->maxLength(255),
                        
                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah')
                            ->prefix('Rp')
                            ->numeric()
                            ->required()
                            ->minValue(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge()
                    ->formatStateUsing(function ($state) {
                        $categories = [
                            'listrik' => 'Listrik',
                            'air' => 'Air',
                            'gaji' => 'Gaji',
                            'atk' => 'ATK',
                            'perlengkapan' => 'Perlengkapan',
                            'pemeliharaan' => 'Pemeliharaan',
                            'transportasi' => 'Transportasi',
                            'lainnya' => 'Lainnya',
                        ];
                        return $categories[$state] ?? ucfirst($state);
                    })
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('description')
                    ->label('Deskripsi')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dibuat Oleh')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategori')
                    ->options([
                        'listrik' => 'Listrik',
                        'air' => 'Air',
                        'gaji' => 'Gaji',
                        'atk' => 'ATK',
                        'perlengkapan' => 'Perlengkapan',
                        'pemeliharaan' => 'Pemeliharaan',
                        'transportasi' => 'Transportasi',
                        'lainnya' => 'Lainnya',
                    ]),
                
                Tables\Filters\Filter::make('date')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('date_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['date_from'], fn (Builder $query, $date) => $query->whereDate('date', '>=', $date))
                            ->when($data['date_until'], fn (Builder $query, $date) => $query->whereDate('date', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'desc')
            ->defaultPaginationPageOption(10)
            ->paginationPageOptions([10, 25, 50, 100]);
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
            'index' => Pages\ListOperationalCosts::route('/'),
            'create' => Pages\CreateOperationalCost::route('/create'),
            'edit' => Pages\EditOperationalCost::route('/{record}/edit'),
        ];
    }
}