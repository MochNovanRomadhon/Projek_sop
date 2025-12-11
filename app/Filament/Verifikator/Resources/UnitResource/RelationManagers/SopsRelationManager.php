<?php

namespace App\Filament\Verifikator\Resources\UnitResource\RelationManagers;

use App\Filament\Verifikator\Resources\UnitResource; // Import ini penting untuk URL
use App\Filament\Verifikator\Resources\SopResource\Pages\ViewSop; // Import View SOP Page
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SopsRelationManager extends RelationManager
{
    protected static string $relationship = 'sops';

    protected static ?string $title = 'Daftar SOP';

    protected static ?string $icon = 'heroicon-o-document-text';

    public function table(Table $table): Table
    {
        return $table
            // 1. FILTER: Jangan tampilkan Riwayat di sini
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'Riwayat'))
            
            // 2. KOLOM (Definisikan manual agar tidak konflik)
            ->columns([
                Tables\Columns\TextColumn::make('judul')
                    ->label('Nama SOP')
                    ->searchable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->date('d M Y')
                    ->label('Tgl Berlaku'),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Disetujui' => 'success',
                        'Menunggu Verifikasi' => 'warning',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    }),
            ])
            
            // 3. TOMBOL HEADER (Disini letak tombol Riwayat)
            ->headerActions([
                Tables\Actions\Action::make('riwayat')
                    ->label('Lihat Riwayat SOP') // Label Tombol
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    // Arahkan ke halaman ListRiwayatSops milik Unit tersebut
                    ->url(fn () => UnitResource::getUrl('riwayat', ['record' => $this->getOwnerRecord()])),
            ]);
    }
}