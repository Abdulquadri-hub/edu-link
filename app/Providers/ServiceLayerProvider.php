<?php

namespace App\Providers;

use App\Services\CourseService;
use App\Services\ParentService;
use App\Services\StudentService;
use App\Services\Auth\HashService;
use App\Services\EnrollmentService;
use App\Services\InstructorService;
use Illuminate\Support\ServiceProvider;
use App\Contracts\Services\HashServiceInterface;
use App\Contracts\Services\CourseServiceInterface;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Services\StudentServiceInterface;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Services\InstructorServiceInterface;

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

        //Instructor Service
        $this->app->bind(
            InstructorServiceInterface::class,
            InstructorService::class
        );

       //Enrollment Service
        $this->app->bind(
            EnrollmentServiceInterface::class,
            EnrollmentService::class
        );

        //Course Service
        $this->app->bind(
            CourseServiceInterface::class,
            CourseService::class
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
