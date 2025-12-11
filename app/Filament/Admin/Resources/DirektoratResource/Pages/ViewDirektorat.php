<?php

namespace App\Filament\Admin\Resources\DirektoratResource\Pages;

use App\Filament\Admin\Resources\DirektoratResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewDirektorat extends ViewRecord
{
    protected static string $resource = DirektoratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tombol Edit di pojok kanan atas
            Actions\EditAction::make(),
            // Tombol Hapus di pojok kanan atas
            Actions\DeleteAction::make(),
        ];
    }
}