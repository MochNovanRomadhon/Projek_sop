<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSop extends CreateRecord
{
    protected static string $resource = SopResource::class;

    // Mengubah Judul Halaman
    protected static ?string $title = 'Ajukan SOP Baru';

    // Mengubah Label Tombol "Create" di bawah form menjadi "Ajukan"
    protected function getCreateFormAction(): \Filament\Actions\Action
    {
        return parent::getCreateFormAction()
            ->label('Ajukan Sekarang')
            ->icon('heroicon-o-paper-airplane');
    }

    // Mengatur agar setelah create, kembali ke list
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Memastikan data yang masuk memiliki status yang benar sebelum disimpan
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['unit_id'] = auth()->user()->unit_id;
        $data['status'] = 'Menunggu Verifikasi'; // Langsung masuk ke verifikasi
        
        return $data;
    }
}