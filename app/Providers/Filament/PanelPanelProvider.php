<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\AccesosRapidosWidget;
use App\Filament\Widgets\ActividadRecienteWidget;
use App\Filament\Widgets\ContabilidadResumenWidget;
use App\Filament\Widgets\DashboardAgendaWidget;
use App\Filament\Widgets\DashboardCasosChartWidget;
use App\Filament\Widgets\DashboardCasosWidget;
use App\Filament\Widgets\DashboardFinanzasHnlChartWidget;
use App\Filament\Widgets\DashboardFinanzasUsdChartWidget;
use App\Filament\Widgets\MisTareasWidget;
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
                'primary' => Color::hex('#276B5D'),
            ])
            ->viteTheme('resources/css/filament/panel/theme.css')
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): string => request()->routeIs('filament.panel.auth.login')
                    ? view('filament.login-visual-panel')->render()
                    : ''
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn (): string => view('filament.topbar-brand')->render()
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_LOGO_AFTER,
                fn (): string => view('filament.sidebar-collapse-buttons')->render()
            )
            ->sidebarCollapsibleOnDesktop()
            ->brandName('Bufete Benitez')
            ->brandLogo(fn () => asset('img/logo.png'))
            ->brandLogoHeight('4.25rem')
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
                DashboardAgendaWidget::class,
                DashboardCasosWidget::class,
                DashboardCasosChartWidget::class,
                DashboardFinanzasHnlChartWidget::class,
                DashboardFinanzasUsdChartWidget::class,
                MisTareasWidget::class,
                AccesosRapidosWidget::class,
                ActividadRecienteWidget::class,
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
