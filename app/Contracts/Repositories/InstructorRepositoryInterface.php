<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\BaseRepositoryInterface;

interface InstructorRepositoryInterface extends BaseRepositoryInterface
{
    public function findByInstructorId(string $instructorId): ?Model;
    public function getAllActive(): Collection;
    public function getWithCourses(int $instructorId): ?Model;
    public function getAssignedStudents(int $instructorId): Collection;
    public function getMonthlyHours(int $instructorId, int $month, int $year): float;
    public function getUpcomingSessions(int $instructorId): Collection;
    public function getPendingGrading(int $instructorId): Collection;
    public function getTeachingCourses(int $instructorId): Collection;
}
