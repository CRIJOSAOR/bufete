<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use App\Filament\Widgets\ContabilidadResumenWidget;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Navigation\NavigationGroup;
use Filament\View\PanelsRenderHook;
class PanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('panel')
            ->path('panel')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
    PanelsRenderHook::STYLES_AFTER,
    fn (): string => '<style>
        .fi-sidebar { background-color: #0f1f3d !important; }

        .fi-sidebar .fi-sidebar-group-label,
        .fi-sidebar .fi-sidebar-item-label { color: #e2e8f0 !important; }

        .fi-sidebar .fi-icon,
        .fi-sidebar .fi-sidebar-group-collapse-btn { color: #94a3b8 !important; }

        .fi-sidebar .fi-sidebar-item-btn:hover { background-color: rgba(255,255,255,.08) !important; }

        .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn { background-color: rgba(255,255,255,.12) !important; }
        .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label,
        .fi-sidebar .fi-sidebar-item.fi-active .fi-icon { color: #b8924a !important; }
    </style>'
)

->renderHook(
    PanelsRenderHook::STYLES_AFTER,
    fn (): string => '<style>
        /* ---------- Menú lateral ---------- */
        .fi-sidebar { background-color: #0f1f3d !important; }

        .fi-sidebar .fi-sidebar-group-label,
        .fi-sidebar .fi-sidebar-item-label { color: #e2e8f0 !important; }

        .fi-sidebar .fi-icon,
        .fi-sidebar .fi-sidebar-group-collapse-btn { color: #94a3b8 !important; }

        .fi-sidebar .fi-sidebar-item-btn:hover { background-color: rgba(255,255,255,.08) !important; }

        .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn { background-color: rgba(255,255,255,.12) !important; }
        .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label,
        .fi-sidebar .fi-sidebar-item.fi-active .fi-icon { color: #b8924a !important; }

        /* ---------- Barra superior ---------- */
        .fi-topbar { background-color: #0f1f3d !important; }

        .fi-topbar .fi-logo { color: #ffffff !important; }

        .fi-topbar .fi-user-menu-trigger-text { color: #e2e8f0 !important; }

        .fi-topbar .fi-topbar-open-sidebar-btn,
        .fi-topbar .fi-topbar-close-sidebar-btn,
        .fi-topbar .fi-topbar-open-collapse-sidebar-btn,
        .fi-topbar .fi-topbar-close-collapse-sidebar-btn,
        .fi-topbar .fi-topbar-open-sidebar-btn .fi-icon,
        .fi-topbar .fi-topbar-close-sidebar-btn .fi-icon,
        .fi-topbar .fi-topbar-open-collapse-sidebar-btn .fi-icon,
        .fi-topbar .fi-topbar-close-collapse-sidebar-btn .fi-icon { color: #cbd5e1 !important; }
    </style>'
)
->brandName('Bufete Legal')
->brandLogo(fn () => asset('img/logo.png'))
->brandLogoHeight('2.5rem')
            ->navigationGroups([
    NavigationGroup::make('Gestión')->collapsible()->collapsed(),
    NavigationGroup::make('Contabilidad')->collapsible()->collapsed(),

    NavigationGroup::make('Administración')->collapsible()->collapsed(),
])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                ContabilidadResumenWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
               FilamentShieldPlugin::make()
        ->navigationGroup('Administración')
        ->navigationSort(2),

            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
