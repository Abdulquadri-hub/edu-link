<?php

namespace App\Contracts\Services;

interface EnrollmentServiceInterface
{
    public function enrollStudent(int $studentId, int $courseId);
    public function unenrollStudent(int $studentId, int $courseId);
    public function updateProgress(int $enrollmentId);
    public function completeEnrollment(int $enrollmentId, float $finalGrade);
}