<?php

namespace App\Services;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;

class EnrollmentService implements EnrollmentServiceInterface
{
    public function __construct(
        private EnrollmentRepositoryInterface $enrollmentRepo,
        private CourseRepositoryInterface $courseRepo
    ) {}

    public function enrollStudent(int $studentId, int $courseId)
    {
        // Check if course is full
        if ($this->courseRepo->isFull($courseId)) {
            throw new \Exception('Course is full');
        }

        // Check if already enrolled
        $existing = $this->enrollmentRepo->getByStudent($studentId)
            ->where('course_id', $courseId)
            ->where('status', 'active')
            ->first();

        if ($existing) {
            throw new \Exception('Student already enrolled in this course');
        }

        return $this->enrollmentRepo->enroll($studentId, $courseId);
    }

    public function unenrollStudent(int $studentId, int $courseId)
    {
        return $this->enrollmentRepo->unenroll($studentId, $courseId);
    }

    public function updateProgress(int $enrollmentId)
    {
        $this->enrollmentRepo->updateProgress($enrollmentId);
    }

    public function completeEnrollment(int $enrollmentId, float $finalGrade)
    {
        $this->enrollmentRepo->markCompleted($enrollmentId, $finalGrade);
    }
}
