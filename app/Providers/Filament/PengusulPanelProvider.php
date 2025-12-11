<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Illuminate\Support\Facades\Blade;

class PengusulPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('pengusul')
            ->path('pengusul')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Pengusul/Resources'), for: 'App\\Filament\\Pengusul\\Resources')
            ->discoverPages(in: app_path('Filament/Pengusul/Pages'), for: 'App\\Filament\\Pengusul\\Pages')
            ->discoverWidgets(in: app_path('Filament/Pengusul/Widgets'), for: 'App\\Filament\\Pengusul\\Widgets')
            
            ->pages([
                Pages\Dashboard::class,
            ])
             ->brandLogo(asset('images/logosop.png'))
            ->brandLogoHeight('2.5rem')
            ->databaseNotifications() // Wajib ada
            ->renderHook(
                'panels::body.end',
                fn () => \Illuminate\Support\Facades\Blade::render("@vite('resources/js/app.js')")
            )
            
            // [1] Hapus Widget Bawaan
            ->widgets([]) 

            // [2] Profil Sidebar Bawah
            ->renderHook(
                'panels::sidebar.footer',
                fn () => view('filament.components.sidebar-profile')
            )

             // [3] PERBAIKAN CSS: Hanya sembunyikan User Menu, BIARKAN Theme Switcher
            ->renderHook(
                'panels::head.end',
                fn () => Blade::render('<style>
                    /* 1. Sembunyikan Menu User (Avatar & Nama) di Kanan Atas */
                    .fi-topbar-item.fi-user-menu { 
                        display: none !important; 
                    }

                    /* 2. Pastikan Theme Switcher TETAP MUNCUL */
                    .fi-topbar-item.fi-theme-switcher { 
                        display: flex !important;
                        visibility: visible !important;
                    }
                    
                    /* Tambahan: Jika tombol notifikasi ikut hilang, tambahkan ini */
                    .fi-topbar-item {
                        display: flex; /* Defaultnya flex, jangan di-none-kan global */
                    }
                </style>')
            )

            // Lonceng Notifikasi
            ->databaseNotifications()
            
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}