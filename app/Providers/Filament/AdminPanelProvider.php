<?php

namespace App\Providers\Filament;

use App\Filament\Resources\ActivityLogs\ActivityLogResource;
use App\Filament\Widgets\LhpChartWidget;
use App\Filament\Widgets\LhpOverviewWidget;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
// use Filament\Auth\Pages\EditProfile;
use App\Filament\Pages\Auth\EditProfile;
use Filament\Http\Middleware\Authenticate;
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
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Rmsramos\Activitylog\ActivitylogPlugin;
use Filament\Navigation\NavigationItem;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->registration()
            ->path('admin')
            ->profile(EditProfile::class, isSimple: false) 
            ->login()
            ->navigationItems([
                NavigationItem::make('My Profile')
                    ->url(fn (): string => EditProfile::getUrl())
                    ->icon('heroicon-o-user')
                    ->group('User Management')
                    ->sort(7)
                ])
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
               
               LhpOverviewWidget::class,
            
          
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
            ->plugins([
                FilamentShieldPlugin::make()
                ->navigationLabel('Roles')
                ->navigationGroup('Settings')
                ->navigationSort(6),
                                    
            ])

             
               ->resources([
            // ... resource lain ...
            ActivityLogResource::class,

        ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
