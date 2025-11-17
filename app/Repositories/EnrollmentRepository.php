<?php

namespace App\Repositories;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;
use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class EnrollmentRepository extends BaseRepository implements EnrollmentRepositoryInterface
{
    public function __construct(Enrollment $model)
    {
        parent::__construct($model);
    }

    public function getActiveEnrollments(): Collection
    {
        return $this->model->active()->get();
    }

    public function getByStudent(int $studentId): Collection
    {
        return $this->model->where('student_id', $studentId)->get();
    }

    public function getByCourse(int $courseId): Collection
    {
        return $this->model->where('course_id', $courseId)->get();
    }

    public function enroll(int $studentId, int $courseId): Model
    {
        return $this->create([
            'student_id' => $studentId,
            'course_id' => $courseId,
            'enrolled_at' => now(),
            'status' => 'active',
        ]);
    }

    public function unenroll(int $studentId, int $courseId): bool
    {
        return $this->model
            ->where('student_id', $studentId)
            ->where('course_id', $courseId)
            ->update(['status' => 'dropped']);
    }

    public function updateProgress(int $enrollmentId): void
    {
        $enrollment = $this->findOrFail($enrollmentId);
        $enrollment->updateProgress();
    }

    public function markCompleted(int $enrollmentId, float $finalGrade): void
    {
        $enrollment = $this->findOrFail($enrollmentId);
        $enrollment->markCompleted($finalGrade);
    }
}

