<?php

namespace App\Filament\Verifikator\Resources\UnitResource\Pages;

use App\Filament\Verifikator\Resources\UnitResource;
use App\Filament\Verifikator\Resources\UnitResource\RelationManagers\RiwayatSopsRelationManager;
use Filament\Resources\Pages\ViewRecord;

class ListRiwayatSops extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected static ?string $title = 'Riwayat SOP (Arsip)';

    protected static ?string $breadcrumb = 'Riwayat';

    // Memanggil tabel khusus riwayat
    public function getRelationManagers(): array
    {
        return [
            RiwayatSopsRelationManager::class,
        ];
    }

    // Hilangkan tombol Edit/Delete default
    protected function getHeaderActions(): array
    {
        return [];
    }
}