<?php

namespace App\Providers;

use App\Models\Warga;
use App\Policies\WargaPolicy;
use App\Models\CctvCamera;
use App\Models\CctvRecording;
use App\Policies\CctvCameraPolicy;
use App\Policies\CctvRecordingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Warga::class, WargaPolicy::class);
        Gate::policy(CctvCamera::class, CctvCameraPolicy::class);
        Gate::policy(CctvRecording::class, CctvRecordingPolicy::class);
    }
}
