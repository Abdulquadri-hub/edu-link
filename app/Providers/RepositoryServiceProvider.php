<?php

namespace App\Providers;

use App\Repositories\ParentRepository;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Repositories\ParentRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Repositories\StudentRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Parent Repository
        $this->app->bind(
            ParentRepositoryInterface::class, ParentRepository::class
        );

        //Student Repository
        $this->app->bind(
            StudentRepositoryInterface::class, StudentRepository::class
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
