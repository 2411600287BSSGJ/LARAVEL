<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Workout;          
use App\Observers\WorkoutObserver;  

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Workout::observe(WorkoutObserver::class); 
    }
}