<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Strategies\UtilizationCalculatorContext;
use App\Strategies\ClassroomUtilizationStrategy;
use App\Strategies\LaboratoryUtilizationStrategy;
use App\Strategies\UtilizationStrategyInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Pola Desain Pendukung (Strategy Pattern):
        // Mendaftarkan seluruh implementasi strategi utilisasi konkret ke dalam UtilizationCalculatorContext.
        // Jika terdapat tipe ruangan baru (misal: auditorium), cukup buat class strateginya dan tambahkan di array ini.
        $this->app->bind(UtilizationCalculatorContext::class, function ($app) {
            $strategies = [
                $app->make(ClassroomUtilizationStrategy::class),
                $app->make(LaboratoryUtilizationStrategy::class),
                $app->make(\App\Strategies\AuditoriumUtilizationStrategy::class),
            ];

            return new UtilizationCalculatorContext($strategies);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
