<?php

namespace App\Filament\Verifikator\Resources\UnitResource\RelationManagers;

use App\Filament\Verifikator\Resources\UnitResource;
use App\Filament\Verifikator\Resources\SopResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SopsRelationManager extends RelationManager
{
    protected static string $relationship = 'sops';

    protected static ?string $title = 'Daftar SOP';

    protected static ?string $icon = 'heroicon-o-document-text';

    // [PENTING] Ini wajib ada untuk fitur pencarian global relation manager
    protected static ?string $recordTitleAttribute = 'judul';

    public function table(Table $table): Table
    {
        return $table
            ->description(fn () => 'Unit: ' . $this->getOwnerRecord()->nama)

            // [SOLUSI] Hapus recordUrl sementara.
            // Penyebab error "layar hitam" biasanya karena route 'view' belum ada saat baris ini diproses.
            // ->recordUrl(fn ($record) => SopResource::getUrl('view', ['record' => $record]))

            // Filter: Jangan tampilkan Riwayat
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'Riwayat'))
            
            ->columns([
                Tables\Columns\TextColumn::make('judul') 
                    ->label('Nama SOP')
                    ->searchable() 
                    ->sortable()
                    ->wrap()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('nomor_sk')
                    ->label('No. SK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_berlaku')
                    ->date('d M Y')
                    ->label('Tgl Berlaku')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Disetujui' => 'success',
                        'Menunggu Verifikasi' => 'warning',
                        'Ditolak' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'Disetujui' => 'Aktif',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'Menunggu Verifikasi' => 'Menunggu Verifikasi',
                        'Disetujui' => 'Aktif',
                        'Ditolak' => 'Ditolak',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('riwayat')
                    ->label('Riwayat SOP')
                    ->icon('heroicon-o-archive-box')
                    ->color('gray')
                    // Gunakan URL '#' agar aman dan tidak crash saat refresh tabel
                    ->url('#') 
                    ->openUrlInNewTab(false),
            ])
            ->actions([
                // [SOLUSI] Gunakan ViewAction default (Modal Popup).
                // Hapus ->url(...) agar Filament tidak memaksa mencari route page yang mungkin belum ada.
                // Ini akan membuka detail SOP dalam modal, yang jauh lebih stabil.
                Tables\Actions\ViewAction::make()
                    ->label('Lihat'),
            ]);
    }
}