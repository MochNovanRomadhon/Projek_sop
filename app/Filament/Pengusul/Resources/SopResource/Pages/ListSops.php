<?php

namespace App\Filament\Pengusul\Resources\SopResource\Pages;

use App\Filament\Pengusul\Resources\SopResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Table;

class ListSops extends ListRecords
{
    protected static string $resource = SopResource::class;

    // [FIX] Menggunakan modifyQueryUsing pada table() sebagai pengganti getTableQuery()
    public function table(Table $table): Table
    {
        // Panggil tabel default dari Resource, lalu tambahkan filter
        return SopResource::table($table)
            ->modifyQueryUsing(fn (Builder $query) => $query->where('status', '!=', 'Riwayat'));
    }

    protected function getHeaderActions(): array
    {
        return [
            // 2. Tombol Ajukan Baru
            Actions\CreateAction::make()
                ->label('Ajukan SOP Baru')
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getSubheading(): ?string
    {
        $user = auth()->user();
        $unitName = $user->unit->nama ?? '-';
        return "Unit Kerja: {$unitName}";
    }
}