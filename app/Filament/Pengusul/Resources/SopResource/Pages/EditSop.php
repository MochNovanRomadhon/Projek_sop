<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditSop extends EditRecord
{
    protected static string $resource = SopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn ($record) => in_array($record->status, ['Draft', 'Ditolak'])),
        ];
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Perubahan berhasil disimpan';
    }
    // [BARU] Mengubah nama tombol "Save changes" menjadi "Simpan Perubahan"
    protected function getSaveFormAction(): \Filament\Actions\Action
    {
        return parent::getSaveFormAction()
            ->label('Simpan Perubahan');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Logika: Jika edit SOP yang sudah 'Disetujui', ubah status jadi 'Menunggu Verifikasi'
        if ($this->getRecord()->status === 'Disetujui') {
            $data['status'] = 'Menunggu Verifikasi';
            $this->isResubmission = true;
        } else {
            $this->isResubmission = false;
        }

        return $data;
    }

    protected $isResubmission = false;

    protected function afterSave(): void
    {
        if ($this->isResubmission) {
            SopResource::sendNotificationToVerifikator($this->getRecord(), 'revisi');
            
            Notification::make()
                ->title('Perubahan Diajukan')
                ->body('Dokumen SOP diajkuakn untuk diproses verifikasi.')
                ->success()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}