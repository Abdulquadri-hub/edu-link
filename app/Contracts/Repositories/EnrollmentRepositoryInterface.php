<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface EnrollmentRepositoryInterface extends BaseRepositoryInterface
{
    public function getActiveEnrollments(): Collection;
    public function getByStudent(int $studentId): Collection;
    public function getByCourse(int $courseId): Collection;
    public function enroll(int $studentId, int $courseId): Model;
    public function unenroll(int $studentId, int $courseId): bool;
    public function updateProgress(int $enrollmentId): void;
    public function markCompleted(int $enrollmentId, float $finalGrade): void;
}