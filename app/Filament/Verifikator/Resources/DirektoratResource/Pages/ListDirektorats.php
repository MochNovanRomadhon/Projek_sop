<?php

namespace App\Filament\Verifikator\Resources\DirektoratResource\Pages;

use App\Filament\Verifikator\Resources\DirektoratResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDirektorats extends ListRecords
{
    protected static string $resource = DirektoratResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
