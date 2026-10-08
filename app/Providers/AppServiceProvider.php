<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use App\Models\KpspResult;
use App\Models\UserMeasurement;
use App\Observers\AssessmentTriggerObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\Growth\ZScoreCalculator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrap();
        \Carbon\Carbon::setLocale('id');

        UserMeasurement::observe(AssessmentTriggerObserver::class);
        KpspResult::observe(AssessmentTriggerObserver::class);
    }
}
