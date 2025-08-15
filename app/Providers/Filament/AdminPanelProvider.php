<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use App\Filament\Widgets\AssetTypeAllocationChart;
use App\Filament\Widgets\MonthlyDividendsChart;
use App\Filament\Widgets\PortfolioPerformanceChart;
use App\Filament\Widgets\PortfolioProfitabilityChart;
use App\Filament\Widgets\PortfolioYieldChart;
use App\Filament\Widgets\MonthlyContributionsChart;
use App\Filament\Widgets\TrueProfitabilityChart;
use App\Filament\Widgets\PortfolioStatsOverview;

/**
 * This class provides the Filament admin panel configuration.
 */

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            //->widgets([
            //    Widgets\AccountWidget::class,
            //    Widgets\FilamentInfoWidget::class,
            //])
            // ADICIONE ESTA SEÇÃO
            ->widgets([
                PortfolioStatsOverview::class,
                AssetTypeAllocationChart::class,
                MonthlyDividendsChart::class,
                PortfolioPerformanceChart::class,
                PortfolioProfitabilityChart::class,
                PortfolioYieldChart::class,
                MonthlyContributionsChart::class,
                TrueProfitabilityChart::class,
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
            ]);
    }
}
