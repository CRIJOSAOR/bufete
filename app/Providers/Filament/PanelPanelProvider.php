<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ContabilidadResumenWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class PanelPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('panel')
            ->path('panel')
            ->login()
            ->sidebarWidth('13rem')
            ->colors([
                'primary' => Color::hex('#174A4A'),
            ])
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => '<style>
                    .fi-sidebar {
                        background-color: #174A4A !important;
                    }

                    .fi-sidebar .fi-sidebar-group-label {
                        color: #C7D6CC !important;
                    }

                    .fi-sidebar .fi-sidebar-item-label {
                        color: #F7F5EF !important;
                    }

                    .fi-sidebar .fi-icon,
                    .fi-sidebar .fi-sidebar-group-collapse-btn {
                        color: #A9C0B4 !important;
                    }

                    .fi-sidebar .fi-sidebar-item-btn:hover {
                        background-color: rgba(247, 245, 239, .08) !important;
                    }

                    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
                        background-color: rgba(185, 130, 90, .16) !important;
                    }

                    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label,
                    .fi-sidebar .fi-sidebar-item.fi-active .fi-icon {
                        color: #D99A70 !important;
                    }

                    .fi-topbar,
                    .fi-main-ctn {
                        background-color: #F7F5EF !important;
                    }

                    .fi-topbar {
                        border-bottom: 1px solid #E5E1D8;
                    }

                    .fi-topbar .fi-user-menu-trigger-text {
                        color: #174A4A !important;
                    }

                    .fi-topbar .fi-topbar-open-sidebar-btn,
                    .fi-topbar .fi-topbar-close-sidebar-btn,
                    .fi-topbar .fi-topbar-open-collapse-sidebar-btn,
                    .fi-topbar .fi-topbar-close-collapse-sidebar-btn,
                    .fi-topbar .fi-topbar-open-sidebar-btn .fi-icon,
                    .fi-topbar .fi-topbar-close-sidebar-btn .fi-icon,
                    .fi-topbar .fi-topbar-open-collapse-sidebar-btn .fi-icon,
                    .fi-topbar .fi-topbar-close-collapse-sidebar-btn .fi-icon {
                        color: #376B68 !important;
                    }
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
