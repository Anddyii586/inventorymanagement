<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Filament\Auth\CustomLogin;
use App\Filament\Pages\EditProfile;
use App\Filament\Widgets\CustomAccountWidget;
use Filament\Support\Colors\Color;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->sidebarCollapsibleOnDesktop()
            ->collapsedSidebarWidth('4rem')
            ->sidebarWidth('16rem')
            ->login(CustomLogin::class)
            ->profile(EditProfile::class)
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->favicon('/logo.png')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                \App\Filament\Widgets\CustomAccountWidget::class,
            ])
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
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->renderHook(
                \Filament\View\PanelsRenderHook::USER_MENU_BEFORE,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex items-center gap-2 mr-3 group px-3 py-1.5 rounded-lg dark:bg-gray-800/50 backdrop-blur-sm transition-all hover:border-indigo-400 dark:hover:border-indigo-500">
                        <div class="flex flex-col items-end">
                            <span class="text-xs font-semibold text-gray-900 dark:text-white leading-none mb-1">
                                {{ auth()->user()->nama }}
                            </span>
                        </div>
                    </div>
                '),
            )
            ->renderHook(
                \Filament\View\PanelsRenderHook::TOPBAR_START,
                fn (): string => \Illuminate\Support\Facades\Blade::render('
                    <div class="flex-1 max-w-2xl overflow-hidden ml-4 hidden lg:block">
                        <div class="relative flex overflow-x-hidden py-1.5 px-4">
                            <div class="animate-marquee whitespace-nowrap py-1">
                                <span class="text-xs font-semibold text-indigo-900 dark:text-indigo-400">
                                    Selamat Datang di Sistem Pendataan Aset PT AIR MINUM GIRI MENANG (PERSERODA), SEMOGA HARI ANDA SELALU BAHAGIA
                                </span>
                            </div>
                        </div>
                    </div>

                    <style>
                        @keyframes marquee {
                            0% { transform: translateX(100%); }
                            100% { transform: translateX(-100%); }
                        }
                        .animate-marquee {
                            animation: marquee 25s linear infinite;
                            display: inline-block;
                        }
                        .animate-marquee:hover {
                            animation-play-state: paused;
                        }
                    </style>
                '),
            );
    }
}
