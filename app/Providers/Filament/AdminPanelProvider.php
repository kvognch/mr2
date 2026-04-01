<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\EditProfile;
use App\Filament\Resources\ContractorResource;
use App\Support\HomepageSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('dashboard')
            ->login()
            ->darkMode(false)
            ->topbar(false)
            ->userMenu(false)
            ->profile(EditProfile::class, isSimple: false)
            ->homeUrl(fn (): string => ContractorResource::getUrl())
            ->colors([
                'primary' => Color::generateV3Palette('#1450a3'),
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => <<<'HTML'
                    <style>
                        .fi-breadcrumbs-item-label {
                            text-transform: none;
                        }
                    </style>
                HTML,
            )
            ->renderHook(
                PanelsRenderHook::STYLES_AFTER,
                fn (): string => app(\Illuminate\Foundation\Vite::class)('resources/css/app.css')->toHtml(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.partials.admin-shell-styles')->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => view('filament.partials.admin-header', [
                    'settings' => HomepageSettings::all(),
                ])->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => view('filament.partials.admin-footer', [
                    'settings' => HomepageSettings::all(),
                ])->render(),
            )
            ->navigationGroups([
                NavigationGroup::make()->label('Подрядчики'),
                NavigationGroup::make()->label('Отзывы'),
                NavigationGroup::make()->label('Заявки'),
                NavigationGroup::make()->label('Пользователи'),
                NavigationGroup::make()->label('Геоданные'),
                NavigationGroup::make()->label('Настройки')->collapsed(),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
