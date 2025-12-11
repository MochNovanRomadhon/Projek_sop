<?php

namespace App\Filament\Admin\Resources\UnitResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RiwayatSopsRelationManager extends RelationManager
{
    protected static string $relationship = 'sops';

    protected static ?string $title = 'Daftar Arsip SOP';

    public function table(Table $table): Table
    {
        return $table
            // Filter Query: Hanya status 'Riwayat'
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'Riwayat'))
            
            ->recordTitleAttribute('judul')
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Nama SOP')
                    ->searchable(),
                
                Tables\Columns\TextColumn::make('nomor_sk')
                    ->label('No. SK'),
                
                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->date('d M Y'),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->label('Diarsipkan Pada'),
            ])
            ->filters([
                // Kosong (sesuai target verifikator)
            ])
            ->headerActions([
                // Kosong (sesuai target verifikator)
            ])
            ->actions([
                Tables\Actions\Action::make('download')
                    ->label('Lihat File')
                    ->icon('heroicon-o-document-text')
                    ->url(fn ($record) => asset('storage/' . $record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Kosong (sesuai target verifikator)
            ]);
    }
}