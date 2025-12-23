<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OperationalCostResource\Pages;
use App\Models\OperationalCost;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OperationalCostResource extends Resource
{
    protected static ?string $model = OperationalCost::class;

    // Ganti icon biar sesuai (uang keluar)
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    
    // Ganti label menu
    protected static ?string $navigationLabel = 'Biaya Operasional';
    protected static ?string $pluralModelLabel = 'Biaya Operasional';
    
    // Urutan menu (biar deket Laporan)
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Input Pengeluaran')
                    ->schema([
                        Forms\Components\DatePicker::make('date')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('category')
                            ->label('Kategori')
                            ->options([
                                'Listrik & Air' => 'Listrik & Air',
                                'Gaji Karyawan' => 'Gaji Karyawan',
                                'Sewa Tempat' => 'Sewa Tempat',
                                'Internet' => 'Internet',
                                'ATK' => 'ATK / Perlengkapan',
                                'Maintenance' => 'Maintenance / Perbaikan',
                                'Lainnya' => 'Lainnya',
                            ])
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('amount')
                            ->label('Jumlah (Rp)')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Textarea::make('description')
                            ->label('Keterangan Detail')
                            ->columnSpanFull(),

                        // Hidden input: Otomatis isi ID user yang login
                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('date')
                    ->date('d M Y')
                    ->label('Tanggal')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategori')
                    ->badge() // Biar warna-warni
                    ->color(fn (string $state): string => match ($state) {
                        'Gaji Karyawan' => 'warning',
                        'Listrik & Air' => 'info',
                        'Lainnya' => 'gray',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('description')
                    ->label('Keterangan')
                    ->limit(30),

                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()->money('IDR'), // Total di bawah tabel
                    ]),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Diinput Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('date', 'desc') // Urutkan dari yang terbaru
            ->filters([
                // Filter berdasarkan Rentang Tanggal
                Tables\Filters\Filter::make('created_at')
                ->form([
                    Forms\Components\DatePicker::make('created_from')->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('created_until')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['created_from'], fn ($query, $date) => $query->whereDate('date', '>=', $date))
                        ->when($data['created_until'], fn ($query, $date) => $query->whereDate('date', '<=', $date));
                })
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOperationalCosts::route('/'),
            'create' => Pages\CreateOperationalCost::route('/create'),
            'edit' => Pages\EditOperationalCost::route('/{record}/edit'),
        ];
    }
    

}