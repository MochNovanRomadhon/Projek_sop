<?php

namespace App\Filament\Pengusul\Widgets;

use App\Models\Sop;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PengusulStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = auth()->user();
        $unitId = $user->unit_id;

        return [
            // 1. Total SOP (Murni) milik Unit ini
            Stat::make('Total SOP', Sop::where('unit_id', $unitId)->where('jenis', 'SOP')->count())
                ->description('Dokumen SOP Internal')
                ->icon('heroicon-o-document-text')
                ->color('primary'),

            // 2. Total SOP AP milik Unit ini
            Stat::make('Total SOP AP', Sop::where('unit_id', $unitId)->where('jenis', 'SOP AP')->count())
                ->description('Administrasi Pemerintahan')
                ->icon('heroicon-o-document-duplicate')
                ->color('info'),

            // 3. Sedang Diproses (Menunggu Verifikasi)
            Stat::make('Diproses Verifikator', Sop::where('unit_id', $unitId)->where('status', 'Menunggu Verifikasi')->count())
                ->description('Menunggu persetujuan')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            // 4. Perlu Review (Draft atau Ditolak)
            Stat::make('Perlu Review', Sop::where('unit_id', $unitId)->whereIn('status', ['Draft', 'Ditolak'])->count())
                ->description('Draft atau Revisi')
                ->icon('heroicon-o-pencil-square')
                ->color('danger'),
        ];
    }
}