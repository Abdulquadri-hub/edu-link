<?php

namespace App\Providers;

use App\Services\ParentService;
use App\Services\Auth\HashService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\HashServiceInterface;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Services\StudentService;

class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Parent Service
        $this->app->bind(
           ParentServiceInterface::class, ParentService::class
        );

        //Student Service
        $this->app->bind(
            StudentServiceInterface::class, StudentService::class
        );

        //Hash Service
        $this->app->bind(
            HashServiceInterface::class, HashService::class
        );
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
