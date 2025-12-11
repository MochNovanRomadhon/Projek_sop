<?php

namespace App\Filament\Verifikator\Widgets;

use App\Models\Direktorat;
use App\Models\Sop;
use App\Models\Unit;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class VerifikatorStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Direktorat', Direktorat::count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Total Unit', Unit::count())
                ->icon('heroicon-o-building-office-2')
                ->color('info'),

            Stat::make('Total SOP Belum Diproses', Sop::where('status', 'Menunggu Verifikasi')->count())
                ->description('SOP perlu tindakan')
                ->icon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Total SOP Ditolak', Sop::where('status', 'Ditolak')->count())
                ->description('Dikembalikan ke Pengusul')
                ->icon('heroicon-o-x-circle')
                ->color('danger'),
        ];
    }
}