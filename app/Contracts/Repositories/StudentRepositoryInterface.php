<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\BaseRepositoryInterface;

interface StudentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByStudentId(string $studentId): ?Model;
    public function getAllActive(): Collection;
    public function getByEnrollmentStatus(string $status): Collection;
    public function getWithParents(int $studentId): ?Model;
    public function getWithCourses(int $studentId): ?Model;
    public function calculateOverallProgress(int $studentId): float;
    public function calculateAttendanceRate(int $studentId): float;
    public function getUpcomingClasses(int $studentId): Collection;
    public function getPendingAssignments(int $studentId): Collection;
    public function getRecentGrades(int $studentId, int $limit = 10): Collection;
}