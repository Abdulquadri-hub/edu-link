<?php

namespace App\Services;
use App\Contracts\Services\EnrollmentServiceInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Student;
use App\Models\Course;

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

    public function calculatePriceFor(Student $student, Course $course, string $frequency): array
    {
        $gradeNumber = $student->academicLevel?->grade_number ?? null;
        if (!$gradeNumber) {
            throw new \Exception('Student does not have an academic level assigned for pricing');
        }

        $isPrimary = $gradeNumber >= 1 && $gradeNumber <= 7;
        $isSecondary = $gradeNumber >= 8 && $gradeNumber <= 12;

        if ($isPrimary) {
            $price = $frequency === '5' || $frequency === '5x' ? 120.00 : 80.00;
        } else {
            $price = $frequency === '5' || $frequency === '5x' ? 150.00 : 100.00;
        }

        return [
            'price' => $price,
            'notes' => [
                'frequency' => $frequency,
                'grade' => $gradeNumber,
            ],
        ];
    }

    /**
     * Static helper for price calculation if DI is not available in the context
     */
    public static function calculatePriceForStatic(Student $student, Course $course, string $frequency): array
    {
        $gradeNumber = $student->academicLevel?->grade_number ?? null;
        if (!$gradeNumber) {
            throw new \Exception('Student does not have an academic level assigned for pricing');
        }

        $isPrimary = $gradeNumber >= 1 && $gradeNumber <= 7;
        $isSecondary = $gradeNumber >= 8 && $gradeNumber <= 12;

        if ($isPrimary) {
            $price = $frequency === '5' || $frequency === '5x' ? 120.00 : 80.00;
        } else {
            $price = $frequency === '5' || $frequency === '5x' ? 150.00 : 100.00;
        }

        return [
            'price' => $price,
            'notes' => [
                'frequency' => $frequency,
                'grade' => $gradeNumber,
            ],
        ];
    }
}
