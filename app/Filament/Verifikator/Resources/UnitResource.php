<?php

namespace App\Filament\Verifikator\Resources;

use App\Filament\Verifikator\Resources\UnitResource\Pages;
use App\Filament\Verifikator\Resources\UnitResource\RelationManagers\SopsRelationManager;
use App\Models\Unit;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;
    
    // Sembunyikan dari sidebar
    protected static bool $shouldRegisterNavigation = false; 

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')->label('Nama Unit')->searchable(),
                Tables\Columns\TextColumn::make('direktorat.nama')->label('Direktorat'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // HANYA Relation Manager UTAMA
            SopsRelationManager::class, 
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'view' => Pages\ViewUnit::route('/{record}'),
            
            // [WAJIB ADA] Rute untuk halaman riwayat
            'riwayat' => Pages\ListRiwayatSops::route('/{record}/riwayat'),
        ];
    }
}