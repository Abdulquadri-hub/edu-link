<?php

namespace App\Repositories;

use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Models\Student;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class StudentRepository extends BaseRepository implements StudentRepositoryInterface
{
    public function __construct(Student $model)
    {
        parent::__construct($model);
    }

    public function findByStudentId(string $studentId): ?Model
    {
        return $this->model->where('student_id', $studentId)->first();
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getByEnrollmentStatus(string $status): Collection
    {
        return $this->model->where('enrollment_status', $status)->get();
    }

    public function getWithParents(int $studentId): ?Model
    {
        return $this->model->with('parents')->find($studentId);
    }

    public function getWithCourses(int $studentId): ?Model
    {
        return $this->model->with('courses')->find($studentId);
    }

    public function calculateOverallProgress(int $studentId): float
    {
        $student = $this->findOrFail($studentId);
        return $student->calculateOverallProgress();
    }

    public function calculateAttendanceRate(int $studentId): float
    {
        $student = $this->findOrFail($studentId);
        return $student->calculateAttendanceRate();
    }

    public function getUpcomingClasses(int $studentId): Collection
    {
        $student = $this->findOrFail($studentId);
        return $student->courses()
            ->with(['classSessions' => function ($query) {
                $query->where('scheduled_at', '>', now())
                      ->where('status', 'scheduled')
                      ->orderBy('scheduled_at');
            }])
            ->get()
            ->pluck('classSessions')
            ->flatten();
    }

    public function getPendingAssignments(int $studentId): Collection
    {
        $student = $this->findOrFail($studentId);
        return $student->courses()
            ->with(['assignments' => function ($query) use ($studentId) {
                $query->where('status', 'published')
                      ->whereDoesntHave('submissions', function ($q) use ($studentId) {
                          $q->where('student_id', $studentId);
                      });
            }])
            ->get()
            ->pluck('assignments')
            ->flatten();
    }

    public function getRecentGrades(int $studentId, int $limit = 10): Collection
    {
        return $this->model->findOrFail($studentId)
            ->grades()
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
