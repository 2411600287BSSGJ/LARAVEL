<?php

namespace App\Providers;

use App\Models\WorkoutPlan;
use App\Policies\WorkoutPlanPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        \App\Models\Workout::observe(\App\Observers\WorkoutObserver::class);
        Gate::policy(WorkoutPlan::class, WorkoutPlanPolicy::class);
    }
}