<?php

namespace App\Filament\Verifikator\Resources\UnitResource\Pages;

use App\Filament\Verifikator\Resources\UnitResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewUnit extends ViewRecord
{
    protected static string $resource = UnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}