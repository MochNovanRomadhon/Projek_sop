<?php

namespace App\Filament\Admin\Resources\SopResource\Pages;

use App\Filament\Admin\Resources\SopResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewSop extends ViewRecord
{
    protected static string $resource = SopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download')
                ->icon('heroicon-o-arrow-down-tray')
                ->label('Unduh PDF')
                ->url(fn ($record) => asset('storage/' . $record->file_path))
                ->openUrlInNewTab(),
        ];
    }
}