<?php

namespace App\Filament\Verifikator\Resources\DirektoratResource\RelationManagers;

use App\Filament\Verifikator\Resources\UnitResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Daftar Unit Kerja';

    public function table(Table $table): Table
    {
        return $table
        ->recordUrl(fn ($record) => UnitResource::getUrl('view', ['record' => $record]))
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->label('Nama Unit')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('sops_count')
                    ->counts('sops')
                    ->label('Total SOP')
                    ->badge()
                    ->color('info'),
            ])
            ->actions([
              
            ]);
    }
}   