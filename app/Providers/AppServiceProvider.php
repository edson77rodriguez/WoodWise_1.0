<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\{
    DashboardMetricService,
    ParcelaService,
    PdfExportService,
    VolumenCalculatorService
};

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(VolumenCalculatorService::class);
        $this->app->singleton(DashboardMetricService::class);
        $this->app->singleton(ParcelaService::class);
        $this->app->singleton(PdfExportService::class);
    }
}