<?php

namespace App\Filament\Verifikator\Resources\DirektoratResource\Pages;

use App\Filament\Verifikator\Resources\DirektoratResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDirektorat extends EditRecord
{
    protected static string $resource = DirektoratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
