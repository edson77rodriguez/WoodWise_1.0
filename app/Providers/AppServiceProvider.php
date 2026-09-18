<?php

namespace App\Providers;
use App\Models\Estimacion;
use App\Models\Estimacion1;
use App\Observers\EstimacionObserver;
use App\Observers\Estimacion1Observer;
use Illuminate\Support\ServiceProvider;
use App\Services\{
    DashboardMetricService,
    ParcelaService,
    FormulaEngineService,
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
        $this->app->singleton(FormulaEngineService::class);
    
    }
}