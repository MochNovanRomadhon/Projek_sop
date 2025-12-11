<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class RevisiSop extends EditRecord
{
    protected static string $resource = SopResource::class;

    protected static ?string $title = 'Perbaiki SOP Ditolak';

    // Hilangkan tombol hapus di header agar user fokus memperbaiki
    protected function getHeaderActions(): array
    {
        return [];
    }

    // Ubah tombol "Save" menjadi "Kirim Revisi"
    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Kirim Revisi')
            ->icon('heroicon-o-paper-airplane')
            ->color('success');
    }

    // Kustomisasi Form: Tambahkan Pesan Error di Atas
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // 1. KOTAK PESAN KESALAHAN (ALASAN PENOLAKAN)
                Forms\Components\Section::make('Pesan Verifikator (Alasan Penolakan)')
                    ->schema([
                        Forms\Components\Placeholder::make('alasan')
                            ->hiddenLabel()
                            ->content(fn ($record) => $record->catatan_revisi ?? 'Tidak ada catatan khusus.')
                            ->extraAttributes([
                                'class' => 'bg-red-50 text-red-700 p-4 rounded-lg border border-red-200 font-bold text-lg'
                            ]),
                    ])
                    ->icon('heroicon-o-exclamation-triangle')
                    ->iconColor('danger')
                    ->collapsible(false),

                // 2. FORM UTAMA (Kita ambil schema dari Resource agar konsisten)
                Forms\Components\Section::make('Perbaikan Data')
                    ->description('Silakan perbaiki data di bawah ini sesuai catatan di atas.')
                    ->schema(SopResource::form($form)->getComponents()),
            ]);
    }

    // LOGIKA PENYIMPANAN
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // 1. Saat disimpan, status otomatis reset jadi 'Menunggu Verifikasi'
        $data['status'] = 'Menunggu Verifikasi';
        
        // 2. Hapus catatan revisi lama karena sudah diperbaiki
        $data['catatan_revisi'] = null;

        return $data;
    }

    protected function afterSave(): void
    {
        // 3. Kirim Notifikasi ke Verifikator
        SopResource::sendNotificationToVerifikator($this->getRecord(), 'revisi');

        Notification::make()
            ->title('Revisi Terkirim')
            ->body('Dokumen Anda telah dikirim ulang ke verifikator.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        // Kembali ke tabel setelah simpan
        return $this->getResource()::getUrl('index');
    }
}