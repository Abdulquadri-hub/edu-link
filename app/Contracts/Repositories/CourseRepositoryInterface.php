<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\BaseRepositoryInterface;

interface CourseRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCourseCode(string $courseCode): ?Model;
    public function getAllActive(): Collection;
    public function getByCategory(string $category): Collection;
    public function getByLevel(string $level): Collection;
    public function getWithInstructors(int $courseId): ?Model;
    public function getEnrolledStudents(int $courseId): Collection;
    public function getAvailableCourses(): Collection;
    public function isFull(int $courseId): bool;
    public function getCompletionRate(int $courseId): float;
}