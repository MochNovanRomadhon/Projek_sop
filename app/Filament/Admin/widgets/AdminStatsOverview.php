<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Direktorat;
use App\Models\Sop;
use App\Models\Unit;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Direktorat', Direktorat::count())
                ->icon('heroicon-o-building-office')
                ->color('primary'),

            Stat::make('Total Unit Kerja', Unit::count())
                ->icon('heroicon-o-building-office-2')
                ->color('info'),

            Stat::make('Total Users', User::count())
                ->icon('heroicon-o-users')
                ->color('success'),

            Stat::make('Total SOP', Sop::where('jenis', 'SOP')->count())
                ->description('Dokumen SOP Murni')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Total SOP AP', Sop::where('jenis', 'SOP AP')->count())
                ->description('Dokumen SOP Admin Pemerintahan')
                ->icon('heroicon-o-document-duplicate')
                ->color('danger'),
        ];
    }
}