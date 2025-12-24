<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Data Master';

    protected static ?string $navigationLabel = 'Produk';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('category_id')
                                    ->relationship('category', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->label('Kategori'),

                                Forms\Components\TextInput::make('code')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->maxLength(255)
                                    ->label('Kode Barang'),
                            ]),

                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Produk'),

                        Forms\Components\FileUpload::make('image')
                            ->image() // Pastiin cuma gambar
                            ->directory('product-images') // Simpen di folder khusus
                            ->visibility('public') // Biar bisa dilihat umum
                            ->label('Foto Produk')
                            ->columnSpanFull(), // Biar lebar

                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\TextInput::make('cost_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        // Only calculate if selling_price is not set, to avoid conflicts
                                        if ($get('selling_price') === null || $get('selling_price') == 0) {
                                            self::calculateSellingPriceFromCostMargin($get, $set);
                                        } else {
                                            self::calculateMarginFromCostSelling($get, $set);
                                        }
                                    })
                                    ->label('HPP'),

                                Forms\Components\TextInput::make('margin_percentage')
                                    ->required()
                                    ->numeric()
                                    ->suffix('%')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        // Only calculate if selling_price is not set, to avoid conflicts
                                        if ($get('selling_price') === null || $get('selling_price') == 0) {
                                            self::calculateSellingPriceFromCostMargin($get, $set);
                                        } else {
                                            self::calculateMarginFromCostSelling($get, $set);
                                        }
                                    })
                                    ->label('Margin'),

                                Forms\Components\TextInput::make('selling_price')
                                    ->required()
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        self::calculateMarginFromCostSelling($get, $set);
                                    })
                                    ->label('Harga Jual'),
                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('stock')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->label('Stok Saat Ini'),

                                Forms\Components\TextInput::make('min_stock')
                                    ->required()
                                    ->numeric()
                                    ->default(5)
                                    ->label('Minimal Stok'),
                            ]),

                        Forms\Components\Toggle::make('is_active')
                            ->required()
                            ->default(true)
                            ->label('Status Aktif'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->label('Kode'),

                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->weight('bold')
                    ->label('Nama Produk'),

                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular(), // Biar bunder (opsional)

                Tables\Columns\TextColumn::make('category.name')
                    ->sortable()
                    ->badge()
                    ->label('Kategori'),

                Tables\Columns\TextColumn::make('selling_price')
                    ->money('IDR')
                    ->sortable()
                    ->label('Harga Jual'),

                Tables\Columns\TextColumn::make('stock')
                    ->numeric()
                    ->sortable()
                    ->color(fn (string $state, Product $record): string => $state < 3 ? 'danger' : ($state <= $record->min_stock ? 'warning' : 'success'))
                    ->label('Stok'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Aktif'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function calculateSellingPriceFromCostMargin(Get $get, Set $set): void
    {
        $cost = (float) $get('cost_price');
        $margin = (float) $get('margin_percentage');
        $sellingPrice = $cost + ($cost * ($margin / 100));
        $set('selling_price', $sellingPrice);
    }

    public static function calculateMarginFromCostSelling(Get $get, Set $set): void
    {
        $cost = (float) $get('cost_price');
        $sellingPrice = (float) $get('selling_price');

        if ($cost > 0) {
            $margin = (($sellingPrice - $cost) / $cost) * 100;
            $set('margin_percentage', $margin);
        }
    }
}