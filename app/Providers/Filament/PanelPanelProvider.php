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
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => '<style>
                    .fi-sidebar {
                        background-color: #173C36 !important;
                    }

                    @media (min-width: 1024px) {
                        .fi-body-has-topbar .fi-sidebar {
                            margin-block-start: calc(-1 * var(--topbar-height)) !important;
                            z-index: 40 !important;
                            top: 0 !important;
                            height: 100dvh !important;
                        }

                        .fi-body-has-topbar .fi-sidebar-header {
                            display: flex !important;
                        }

                        .fi-body-has-topbar .fi-sidebar-header {
                            height: auto !important;
                            min-height: 6rem;
                            gap: .5rem;
                            padding: .75rem 1rem;
                            border-bottom: 1px solid rgba(247, 245, 239, .14);
                        }

                        .fi-body-has-topbar .fi-sidebar-header-logo-ctn {
                            justify-content: center;
                        }

                        .fi-body-has-topbar .fi-sidebar-header-logo-ctn .fi-logo {
                            height: 4.25rem !important;
                            max-width: 9rem;
                            padding: .3rem .65rem;
                            border-radius: .75rem;
                            background-color: #F7F5EF;
                            box-shadow: 0 2px 8px rgba(0, 0, 0, .14);
                            object-fit: contain;
                        }

                        .fi-body-has-topbar .fi-topbar-start {
                            display: none !important;
                        }
                    }

                    .dark .fi-sidebar {
                        background-color: #112F2B !important;
                    }

                    .fi-sidebar .fi-sidebar-group-label {
                        color: #C7D6CC !important;
                    }

                    .fi-sidebar .fi-sidebar-item-label {
                        color: #F4F5EF !important;
                    }

                    .fi-sidebar .fi-icon,
                    .fi-sidebar .fi-sidebar-group-collapse-btn {
                        color: #B8C9BF !important;
                    }

                    .fi-sidebar .fi-sidebar-item-btn:hover {
                        background-color: rgba(247, 245, 239, .1) !important;
                    }

                    .fi-sidebar .fi-sidebar-item.fi-active > .fi-sidebar-item-btn {
                        background-color: rgba(215, 181, 109, .18) !important;
                    }

                    .fi-sidebar .fi-sidebar-item.fi-active .fi-sidebar-item-label,
                    .fi-sidebar .fi-sidebar-item.fi-active .fi-icon {
                        color: #E8CB86 !important;
                    }

                    .fi-topbar,
                    .fi-main-ctn {
                        background-color: #F5F3EE !important;
                    }

                    .fi-topbar {
                        border-bottom: 1px solid #E5E2D9;
                    }

                    .fi-topbar .fi-user-menu-trigger-text {
                        color: #173C36 !important;
                    }

                    .fi-topbar-brand {
                        display: grid;
                        gap: .15rem;
                        margin-inline-start: var(--collapsed-sidebar-width);
                        line-height: 1.15;
                    }

                    .fi-topbar-brand-name {
                        color: #173C36;
                        font-family: Georgia, serif;
                        font-size: .95rem;
                        font-weight: 700;
                        letter-spacing: .025em;
                    }

                    .fi-topbar-brand-tagline {
                        color: #9B7939;
                        font-size: .6rem;
                        font-weight: 600;
                        letter-spacing: .12em;
                        text-transform: uppercase;
                    }

                    .fi-body:has(.fi-sidebar.fi-sidebar-open) .fi-topbar-brand {
                        margin-inline-start: var(--sidebar-width);
                    }

                    .dark .fi-topbar,
                    .dark .fi-main-ctn {
                        background-color: #111A18 !important;
                    }

                    .dark .fi-topbar {
                        border-bottom-color: #2A3A35;
                    }

                    .dark .fi-topbar .fi-user-menu-trigger-text {
                        color: #E4ECE6 !important;
                    }

                    .dark .fi-topbar-brand-name {
                        color: #E4ECE6;
                    }

                    .dark .fi-topbar-brand-tagline {
                        color: #D7B56D;
                    }

                    @media (max-width: 1023px) {
                        .fi-topbar-brand {
                            display: none;
                        }
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

                    .dashboard-agenda-grid {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: 1rem;
                    }

                    .dashboard-agenda-group {
                        min-width: 0;
                        overflow: hidden;
                        border: 1px solid #E7E8E3;
                        border-radius: .9rem;
                        background: #FFFFFF;
                    }

                    .dark .dashboard-agenda-group {
                        border-color: #2A3A35;
                        background: #151F1C;
                    }

                    .dashboard-agenda-group-header {
                        display: flex;
                        align-items: center;
                        justify-content: space-between;
                        gap: .75rem;
                        min-height: 4.25rem;
                        padding: .75rem 1rem;
                        border-bottom: 1px solid #ECEDE9;
                        background: #FBFBF9;
                    }

                    .dark .dashboard-agenda-group-header {
                        border-bottom-color: #2A3A35;
                        background: #18231F;
                    }

                    .dashboard-agenda-group-title {
                        display: flex;
                        min-width: 0;
                        align-items: center;
                        gap: .7rem;
                    }

                    .dashboard-agenda-dot {
                        width: .55rem;
                        height: .55rem;
                        flex: none;
                        border-radius: 999px;
                    }

                    .dashboard-agenda-dot-danger { background: #C65C53; }
                    .dashboard-agenda-dot-primary { background: #397B69; }
                    .dashboard-agenda-dot-warning { background: #BD9143; }

                    .dashboard-agenda-group-name {
                        overflow: hidden;
                        color: #24352F;
                        font-size: .875rem;
                        font-weight: 650;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    .dark .dashboard-agenda-group-name { color: #E8EEE9; }

                    .dashboard-agenda-group-description {
                        margin-top: .15rem;
                        color: #7A837D;
                        font-size: .72rem;
                    }

                    .dark .dashboard-agenda-group-description { color: #9BAAA1; }

                    .dashboard-agenda-count {
                        display: inline-flex;
                        min-width: 1.8rem;
                        height: 1.8rem;
                        flex: none;
                        align-items: center;
                        justify-content: center;
                        padding-inline: .5rem;
                        border-radius: 999px;
                        font-size: .75rem;
                        font-weight: 700;
                        font-variant-numeric: tabular-nums;
                    }

                    .dashboard-agenda-count-danger { background: #FCECE9; color: #A5423C; }
                    .dashboard-agenda-count-primary { background: #E8F2ED; color: #276B5D; }
                    .dashboard-agenda-count-warning { background: #F8F0DF; color: #8A682F; }
                    .dark .dashboard-agenda-count-danger { background: rgba(198, 92, 83, .16); color: #F2A39A; }
                    .dark .dashboard-agenda-count-primary { background: rgba(57, 123, 105, .2); color: #A4D0BD; }
                    .dark .dashboard-agenda-count-warning { background: rgba(189, 145, 67, .18); color: #E7C783; }

                    .dashboard-agenda-list {
                        margin: 0;
                        padding: 0;
                        list-style: none;
                    }

                    .dashboard-agenda-item {
                        display: flex;
                        min-width: 0;
                        align-items: center;
                        gap: .75rem;
                        padding: .75rem 1rem;
                    }

                    .dashboard-agenda-item + .dashboard-agenda-item {
                        border-top: 1px solid #F0F1EE;
                    }

                    .dark .dashboard-agenda-item + .dashboard-agenda-item {
                        border-top-color: rgba(255, 255, 255, .06);
                    }

                    .dashboard-agenda-date {
                        display: flex;
                        width: 2.85rem;
                        min-height: 3.1rem;
                        flex: none;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                        border-radius: .7rem;
                        line-height: 1;
                    }

                    .dashboard-agenda-date-danger { background: #FCECE9; color: #A5423C; }
                    .dashboard-agenda-date-primary { background: #E8F2ED; color: #276B5D; }
                    .dashboard-agenda-date-warning { background: #F8F0DF; color: #8A682F; }
                    .dark .dashboard-agenda-date-danger { background: rgba(198, 92, 83, .16); color: #F2A39A; }
                    .dark .dashboard-agenda-date-primary { background: rgba(57, 123, 105, .2); color: #A4D0BD; }
                    .dark .dashboard-agenda-date-warning { background: rgba(189, 145, 67, .18); color: #E7C783; }

                    .dashboard-agenda-date-day {
                        font-size: 1.05rem;
                        font-weight: 700;
                        font-variant-numeric: tabular-nums;
                    }

                    .dashboard-agenda-date-month {
                        margin-top: .3rem;
                        font-size: .58rem;
                        font-weight: 650;
                        letter-spacing: .06em;
                        text-transform: uppercase;
                    }

                    .dashboard-agenda-item-content {
                        min-width: 0;
                        flex: 1;
                    }

                    .dashboard-agenda-item-title {
                        display: block;
                        overflow: hidden;
                        color: #25352F;
                        font-size: .82rem;
                        font-weight: 600;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    a.dashboard-agenda-item-title:hover { color: #276B5D; }
                    .dark .dashboard-agenda-item-title { color: #EDF2EE; }
                    .dark a.dashboard-agenda-item-title:hover { color: #A4D0BD; }

                    .dashboard-agenda-item-case {
                        overflow: hidden;
                        margin-top: .2rem;
                        color: #7A837D;
                        font-size: .7rem;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }

                    .dark .dashboard-agenda-item-case { color: #9BAAA1; }

                    .dashboard-agenda-item-time {
                        flex: none;
                        color: #6D7771;
                        font-size: .72rem;
                        font-weight: 600;
                        font-variant-numeric: tabular-nums;
                    }

                    .dark .dashboard-agenda-item-time { color: #B5C0B9; }

                    .dashboard-agenda-empty {
                        display: flex;
                        min-height: 5.25rem;
                        align-items: center;
                        gap: .75rem;
                        padding: .85rem 1rem;
                    }

                    .dashboard-agenda-empty-icon {
                        display: inline-flex;
                        width: 2.25rem;
                        height: 2.25rem;
                        flex: none;
                        align-items: center;
                        justify-content: center;
                        border-radius: 999px;
                        background: #F2F4F1;
                        color: #849089;
                    }

                    .dashboard-agenda-empty-icon svg {
                        width: 1.15rem;
                        height: 1.15rem;
                    }

                    .dark .dashboard-agenda-empty-icon {
                        background: rgba(255, 255, 255, .06);
                        color: #AAB7AF;
                    }

                    .dashboard-agenda-empty p {
                        color: #727D76;
                        font-size: .78rem;
                    }

                    .dark .dashboard-agenda-empty p { color: #AAB7AF; }

                    .dashboard-quick-actions-grid {
                        display: grid;
                        grid-template-columns: repeat(2, minmax(0, 1fr));
                        gap: .7rem;
                    }

                    .dashboard-quick-action {
                        display: flex;
                        min-width: 0;
                        min-height: 5rem;
                        align-items: center;
                        gap: .7rem;
                        padding: .8rem;
                        border: 1px solid #E7E8E3;
                        border-radius: .85rem;
                        background: #FFFFFF;
                        color: inherit;
                        text-decoration: none;
                        transition: border-color .16s ease, box-shadow .16s ease, transform .16s ease, background-color .16s ease;
                    }

                    .dashboard-quick-action:hover {
                        transform: translateY(-2px);
                        border-color: #A9C9BB;
                        background: #FBFDFC;
                        box-shadow: 0 7px 18px rgba(23, 60, 54, .08);
                    }

                    .dashboard-quick-action:focus-visible {
                        outline: 2px solid #397B69;
                        outline-offset: 2px;
                    }

                    .dark .dashboard-quick-action {
                        border-color: #2A3A35;
                        background: #151F1C;
                    }

                    .dark .dashboard-quick-action:hover {
                        border-color: #4F8272;
                        background: #192824;
                        box-shadow: 0 8px 20px rgba(0, 0, 0, .18);
                    }

                    .dashboard-quick-action-icon {
                        display: inline-flex;
                        width: 2.5rem;
                        height: 2.5rem;
                        flex: none;
                        align-items: center;
                        justify-content: center;
                        border: 1px solid #E3EEE8;
                        border-radius: .75rem;
                        background: #EFF6F2;
                        color: #276B5D;
                    }

                    .dashboard-quick-action-icon svg {
                        width: 1.2rem;
                        height: 1.2rem;
                    }

                    .dark .dashboard-quick-action-icon {
                        border-color: rgba(164, 208, 189, .12);
                        background: rgba(57, 123, 105, .18);
                        color: #A4D0BD;
                    }

                    .dashboard-quick-action-copy {
                        min-width: 0;
                        flex: 1;
                    }

                    .dashboard-quick-action-title {
                        display: block;
                        color: #263831;
                        font-size: .8rem;
                        font-weight: 650;
                        line-height: 1.25;
                    }

                    .dark .dashboard-quick-action-title { color: #E8EEE9; }

                    .dashboard-quick-action-description {
                        display: block;
                        margin-top: .25rem;
                        color: #7A837D;
                        font-size: .68rem;
                        line-height: 1.4;
                    }

                    .dark .dashboard-quick-action-description { color: #9BAAA1; }

                    .dashboard-quick-action-arrow {
                        width: .9rem;
                        height: .9rem;
                        flex: none;
                        color: #98A29B;
                        transition: transform .16s ease, color .16s ease;
                    }

                    .dashboard-quick-action:hover .dashboard-quick-action-arrow {
                        transform: translate(2px, -2px);
                        color: #276B5D;
                    }

                    .dark .dashboard-quick-action:hover .dashboard-quick-action-arrow { color: #A4D0BD; }

                    @media (min-width: 1500px) {
                        .dashboard-agenda-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
                    }

                    @media (max-width: 760px) {
                        .dashboard-agenda-grid { grid-template-columns: minmax(0, 1fr); }
                    }

                    @media (max-width: 520px) {
                        .dashboard-quick-actions-grid { grid-template-columns: minmax(0, 1fr); }
                    }

                    .fi-body:has(.fi-login-visual-panel) {
                        background-color: #F7F5EF !important;
                        scroll-behavior: auto !important;
                    }

                    .fi-body:has(.fi-login-visual-panel) *,
                    .fi-body:has(.fi-login-visual-panel) *::before,
                    .fi-body:has(.fi-login-visual-panel) *::after {
                        animation: none !important;
                        scroll-behavior: auto !important;
                        transition: none !important;
                    }

                    html:has(.fi-login-visual-panel),
                    body:has(.fi-login-visual-panel) {
                        width: 100%;
                        height: 100%;
                        overflow: hidden !important;
                    }

                    .fi-body:has(.fi-login-visual-panel) {
                        position: fixed;
                        inset: 0;
                        width: 100%;
                        height: 100vh;
                        height: 100dvh;
                        min-height: 0;
                        overflow: hidden !important;
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-layout {
                        display: grid;
                        grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr);
                        grid-template-rows: minmax(0, 1fr);
                        align-items: stretch;
                        width: 100%;
                        height: 100vh;
                        height: 100dvh;
                        min-height: 0;
                        overflow: hidden;
                        background-color: #F7F5EF;
                    }

                    .fi-login-visual-panel {
                        position: relative;
                        display: flex;
                        grid-column: 1;
                        grid-row: 1;
                        align-self: stretch;
                        align-items: center;
                        justify-content: center;
                        min-width: 0;
                        overflow: hidden;
                        padding: clamp(2rem, 6vw, 6rem);
                        isolation: isolate;
                        background:
                            radial-gradient(ellipse at 12% 88%, rgba(215, 181, 109, .18), transparent 34%),
                            linear-gradient(145deg, #1B453D, #112F2B);
                        color: #F7F5EF;
                    }

                    .fi-login-visual-panel::after {
                        position: absolute;
                        z-index: -1;
                        right: -9rem;
                        bottom: -13rem;
                        width: 30rem;
                        height: 30rem;
                        border: 1px solid rgba(215, 181, 109, .2);
                        border-radius: 50%;
                        box-shadow: 0 0 0 2rem rgba(215, 181, 109, .035), 0 0 0 4rem rgba(215, 181, 109, .025);
                        content: "";
                    }

                    .fi-login-visual-panel-content {
                        width: min(100%, 34rem);
                    }

                    .fi-login-visual-panel-logo {
                        display: block;
                        width: min(100%, 15rem);
                        height: auto;
                        margin-block-end: clamp(2.5rem, 7vh, 5rem);
                        padding: .65rem 1rem;
                        border-radius: 1rem;
                        background: #F7F5EF;
                        box-shadow: 0 1rem 2.5rem rgba(0, 0, 0, .16);
                    }

                    .fi-login-visual-panel-brand {
                        margin: 0 0 1rem;
                        color: #F7F5EF;
                        font-family: Georgia, serif;
                        font-size: clamp(2rem, 3.8vw, 3.5rem);
                        font-weight: 600;
                        letter-spacing: -.04em;
                        line-height: 1;
                    }

                    .fi-login-visual-panel-eyebrow {
                        margin: 0 0 1rem;
                        color: #E8CB86;
                        font-size: .7rem;
                        font-weight: 700;
                        letter-spacing: .2em;
                        text-transform: uppercase;
                    }

                    .fi-login-visual-panel-heading {
                        max-width: 11ch;
                        margin: 0;
                        color: #F7F5EF;
                        font-family: Georgia, serif;
                        font-size: clamp(2.4rem, 4.2vw, 4rem);
                        font-weight: 500;
                        letter-spacing: -.035em;
                        line-height: 1.08;
                    }

                    .fi-login-visual-panel-description {
                        max-width: 34rem;
                        margin: 1.5rem 0 0;
                        color: rgba(247, 245, 239, .76);
                        font-size: 1rem;
                        line-height: 1.8;
                    }

                    .fi-login-visual-panel-caption {
                        display: flex;
                        align-items: center;
                        gap: .65rem;
                        margin: clamp(2.5rem, 7vh, 5rem) 0 0;
                        color: rgba(247, 245, 239, .68);
                        font-size: .72rem;
                        font-weight: 600;
                        letter-spacing: .12em;
                        text-transform: uppercase;
                    }

                    .fi-login-visual-panel-caption::before {
                        width: 1.75rem;
                        height: 1px;
                        background-color: #D7B56D;
                        content: "";
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-main-ctn {
                        display: grid;
                        grid-column: 2;
                        grid-row: 1;
                        grid-template-columns: minmax(0, 1fr);
                        align-self: stretch;
                        place-items: center;
                        min-width: 0;
                        padding: clamp(1.5rem, 4vw, 4rem);
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-main {
                        width: 100%;
                        max-width: 35rem;
                        padding: 0;
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-page-content {
                        padding: clamp(1.75rem, 4vw, 3rem);
                        border: 1px solid #E8E4DA;
                        border-radius: 1.5rem;
                        background-color: #FFFFFF;
                        box-shadow: 0 1.5rem 4rem rgba(23, 60, 54, .09);
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-header {
                        gap: .75rem;
                        margin-block-end: 2rem;
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-header .fi-logo {
                        display: none;
                    }

                    .fi-body:has(.fi-login-visual-panel) .fi-simple-header-heading {
                        color: #173C36;
                        font-family: Georgia, serif;
                        font-size: clamp(1.8rem, 3vw, 2.25rem);
                        font-weight: 600;
                        letter-spacing: -.025em;
                    }

                    .dark .fi-body:has(.fi-login-visual-panel),
                    .dark .fi-body:has(.fi-login-visual-panel) .fi-simple-layout {
                        background-color: #111A18 !important;
                    }

                    .dark .fi-body:has(.fi-login-visual-panel) .fi-simple-page-content {
                        border-color: #2A3A35;
                        background-color: #18231F;
                        box-shadow: 0 1.5rem 4rem rgba(0, 0, 0, .24);
                    }

                    .dark .fi-body:has(.fi-login-visual-panel) .fi-simple-header-heading {
                        color: #F0F2EC;
                    }

                    @media (max-width: 900px) {
                            .fi-body:has(.fi-login-visual-panel) .fi-simple-layout {
                                grid-template-columns: minmax(0, 1fr);
                                grid-template-rows: minmax(10rem, 26dvh) minmax(0, 1fr);
                                align-content: stretch;
                            }

                            .fi-login-visual-panel {
                                grid-column: 1;
                                grid-row: 1;
                                justify-content: flex-start;
                                min-height: 0;
                                padding: 1.5rem clamp(1.25rem, 5vw, 3rem);
                            }

                        .fi-login-visual-panel-content {
                            display: grid;
                            grid-template-columns: clamp(5.5rem, 19vw, 9rem) minmax(0, 1fr);
                            align-items: center;
                            column-gap: 1.5rem;
                            width: min(100%, 42rem);
                        }

                        .fi-login-visual-panel-logo {
                            grid-row: span 3;
                            width: clamp(6.5rem, 19vw, 9rem);
                            margin: 0;
                            padding: .45rem;
                            border-radius: .75rem;
                        }

                        .fi-login-visual-panel-eyebrow {
                            margin-block-end: .4rem;
                            font-size: .6rem;
                        }

                        .fi-login-visual-panel-brand {
                            grid-column: 2;
                            margin-block-end: .45rem;
                            font-size: clamp(1.45rem, 4vw, 2rem);
                        }

                        .fi-login-visual-panel-heading {
                            max-width: none;
                            font-size: clamp(1.35rem, 4vw, 2rem);
                        }

                        .fi-login-visual-panel-description {
                            grid-column: 2;
                            margin-block-start: .45rem;
                            font-size: .82rem;
                            line-height: 1.5;
                        }

                        .fi-login-visual-panel-caption {
                            display: none;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-main-ctn {
                            grid-column: 1;
                            grid-row: 2;
                            height: 100%;
                            min-height: 0;
                            padding: clamp(1.25rem, 5vw, 2.5rem);
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-main {
                            max-width: 32rem;
                        }
                    }

                    @media (max-width: 480px) {
                        .fi-login-visual-panel {
                            padding: 1rem;
                        }

                        .fi-login-visual-panel-content {
                            column-gap: 1rem;
                        }

                        .fi-login-visual-panel-logo {
                            width: 5.5rem;
                        }

                        .fi-login-visual-panel-brand {
                            font-size: clamp(1.25rem, 6vw, 1.7rem);
                        }

                        .fi-login-visual-panel-description {
                            display: none;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-main-ctn {
                            padding: 1rem;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-page-content {
                            padding: 1.5rem;
                            border-radius: 1.125rem;
                        }
                    }

                    @media (max-height: 760px) {
                        .fi-login-visual-panel {
                            padding-block: 1.5rem;
                        }

                        .fi-login-visual-panel-logo {
                            width: min(100%, 10rem);
                            margin-block-end: 1rem;
                            padding: .4rem .65rem;
                            border-radius: .75rem;
                        }

                        .fi-login-visual-panel-brand {
                            margin-block-end: .65rem;
                            font-size: clamp(1.8rem, 4vw, 2.6rem);
                        }

                        .fi-login-visual-panel-eyebrow {
                            margin-block-end: .5rem;
                        }

                        .fi-login-visual-panel-heading {
                            font-size: clamp(2rem, 5vh, 2.8rem);
                        }

                        .fi-login-visual-panel-description {
                            margin-block-start: .75rem;
                            font-size: .9rem;
                            line-height: 1.55;
                        }

                        .fi-login-visual-panel-caption {
                            margin-block-start: 1.5rem;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-main-ctn {
                            padding-block: 1rem;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-page-content {
                            padding-block: 1.5rem;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-header {
                            margin-block-end: 1.25rem;
                        }
                    }

                    @media (max-width: 900px) and (max-height: 760px) {
                        .fi-body:has(.fi-login-visual-panel) .fi-simple-layout {
                            grid-template-rows: minmax(7rem, 23dvh) minmax(0, 1fr);
                        }

                        .fi-login-visual-panel-content {
                            grid-template-rows: auto auto 1fr;
                        }

                        .fi-login-visual-panel-heading {
                            font-size: clamp(1.2rem, 3vh, 1.7rem);
                        }

                        .fi-login-visual-panel-brand {
                            grid-column: 2;
                            font-size: clamp(1.2rem, 4vw, 1.6rem);
                        }

                        .fi-login-visual-panel-description {
                            display: none;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-page-content {
                            padding: 1rem;
                        }

                        .fi-body:has(.fi-login-visual-panel) .fi-simple-header {
                            margin-block-end: 1rem;
                        }
                    }
                </style>'
            )
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
            ->brandLogoHeight('12rem')
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
