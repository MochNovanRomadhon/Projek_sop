<?php

namespace App\Filament\Verifikator\Resources;

use App\Filament\Verifikator\Resources\DirektoratResource\Pages;
use App\Filament\Verifikator\Resources\DirektoratResource\RelationManagers\UnitsRelationManager;
use App\Models\Direktorat;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Infolists; // Import Layout View
use Filament\Infolists\Infolist; // Import Layout View

class DirektoratResource extends Resource
{
    protected static ?string $model = Direktorat::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Data Direktorat';
    protected static ?string $pluralModelLabel = 'Direktorat';

    // Verifikator Tetap Read Only
    public static function canCreate(): bool { return false; }
    public static function canEdit($record): bool { return false; }
    public static function canDelete($record): bool { return false; }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Direktorat')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    // [SAMAKAN] Tambahkan Infolist agar tampilan View sama dengan Admin
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Direktorat')
                    ->schema([
                        Infolists\Components\TextEntry::make('nama')
                            ->label('Nama Direktorat')
                            ->weight(FontWeight::Bold)
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),
                        
                        Infolists\Components\TextEntry::make('units_count')
                            ->state(fn ($record) => $record->units()->count() . ' Unit')
                            ->label('Jumlah Unit')
                            ->badge()
                            ->color('info'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // [SAMAKAN] Klik baris untuk melihat detail (View)
            ->recordUrl(fn (Direktorat $record) => Pages\ViewDirektorat::getUrl([$record]))
            ->columns([
                // [SAMAKAN] Style kolom disamakan dengan Admin
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Direktorat')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Total Unit')
                    ->badge()
                    ->formatStateUsing(fn ($state) => "{$state} Unit")
                    ->color('info')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                // Tidak ada tombol Edit/Hapus karena Verifikator Read Only
                // Tombol View juga tidak perlu karena sudah ada recordUrl (klik baris)
            ])
            ->bulkActions([
                // Tidak ada bulk action
            ]);
    }

    public static function getRelations(): array
    {
        return [
            UnitsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirektorats::route('/'),
            'view' => Pages\ViewDirektorat::route('/{record}'),
        ];
    }
}