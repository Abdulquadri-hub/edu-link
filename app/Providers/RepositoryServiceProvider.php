<?php

namespace App\Providers;

use App\Models\Enrollment;
use App\Repositories\CourseRepository;
use App\Repositories\ParentRepository;
use App\Repositories\StudentRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\EnrollmentRepository;
use App\Repositories\InstructorRepository;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Contracts\Repositories\ParentRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\InstructorRepositoryInterface;

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

        //Instructor Repository
        $this->app->bind(
            InstructorRepositoryInterface::class,
            InstructorRepository::class
        );

        //Enrollment Repository
        $this->app->bind(
            EnrollmentRepositoryInterface::class,
            EnrollmentRepository::class
        );

         //Course Repository
        $this->app->bind(
            CourseRepositoryInterface::class,
            CourseRepository::class
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
