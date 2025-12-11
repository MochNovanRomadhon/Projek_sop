<?php

namespace App\Filament\Verifikator\Resources\SopResource\Pages;

use App\Filament\Verifikator\Resources\SopResource;
use App\Models\Sop;
use App\Models\User; // Import User
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewSop extends ViewRecord
{
    protected static string $resource = SopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // === TOMBOL TOLAK (Merah) ===
            Actions\Action::make('tolak')
                ->label('Tolak / Revisi')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->visible(fn (Sop $record) => $record->status === 'Menunggu Verifikasi')
                ->form([
                    Forms\Components\Textarea::make('catatan_revisi')
                        ->label('Catatan Revisi (Wajib Diisi)')
                        ->required()
                        ->rows(4),
                ])
                ->action(function (Sop $record, array $data) {
                    // 1. Update Status
                    $record->update([
                        'status' => 'Ditolak',
                        'catatan_revisi' => $data['catatan_revisi']
                    ]);

                    // 2. Kirim Notifikasi MERAH ke Pengusul Unit Terkait
                    // Cari user role 'pengusul' yang unit_id nya sama dengan SOP ini
                    $pengusul = User::role('pengusul')
                        ->where('unit_id', $record->unit_id)
                        ->get();

                    Notification::make()
                        ->title('SOP Ditolak & Perlu Revisi')
                        ->body("Catatan: {$data['catatan_revisi']}") // Isi revisi masuk notif
                        ->danger() // Warna Merah
                        ->icon('heroicon-o-exclamation-circle')
                        ->sendToDatabase($pengusul);

                    // 3. Feedback ke Verifikator
                    Notification::make()->title('SOP Ditolak')->danger()->send();
                }),

            // === TOMBOL TERIMA (Hijau) ===
            Actions\Action::make('terima')
                ->label('Terima / Setujui')
                ->color('success')
                ->icon('heroicon-o-check-circle')
                ->visible(fn (Sop $record) => $record->status === 'Menunggu Verifikasi')
                ->requiresConfirmation()
                ->action(function (Sop $record) {
                    // 1. Update Status
                    $record->update([
                        'status' => 'Disetujui', 
                        'catatan_revisi' => null,
                        'tanggal_verifikasi' => now(), // <--- PENTING! Simpan tanggal saat ini
                    ]);

                    // 2. Kirim Notifikasi SELAMAT ke Pengusul
                    $pengusul = User::role('pengusul')
                        ->where('unit_id', $record->unit_id)
                        ->get();

                    Notification::make()
                        ->title('Selamat! SOP Diterima')
                        ->body("SOP '{$record->judul}' telah disetujui dan statusnya kini Aktif.")
                        ->success() // Warna Hijau
                        ->icon('heroicon-o-sparkles')
                        ->sendToDatabase($pengusul);

                    // 3. Feedback ke Verifikator
                    Notification::make()->title('SOP Disetujui')->success()->send();
                }),
        ];
    }
}