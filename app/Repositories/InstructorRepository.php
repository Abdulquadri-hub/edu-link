<?php

namespace App\Repositories;

use App\Models\Instructor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\InstructorRepositoryInterface;

class InstructorRepository extends BaseRepository Implements InstructorRepositoryInterface
{
    public function __construct(Instructor $model)
    {
        parent::__construct($model);
    }

    public function findByInstructorId(string $instructorId): ?Model
    {
        return $this->model->where('instructor_id', $instructorId)->first();
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getWithCourses(int $instructorId): ?Model
    {
        return $this->model->with('courses')->find($instructorId);
    }

    public function getAssignedStudents(int $instructorId): Collection
    {
        $instructor = $this->findOrFail($instructorId);
        return $instructor->courses()
            ->with('students')
            ->get()
            ->pluck('students')
            ->flatten()
            ->unique('id');
    }

    public function getMonthlyHours(int $instructorId, int $month, int $year): float
    {
        $instructor = $this->findOrFail($instructorId);
        return $instructor->calculateMonthlyHours($month, $year);
    }

    public function getUpcomingSessions(int $instructorId): Collection
    {
        return $this->model->findOrFail($instructorId)
            ->classSessions()
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('scheduled_at')
            ->get();
    }

    public function getPendingGrading(int $instructorId): Collection
    {
        return $this->model->findOrFail($instructorId)
            ->assignments()
            ->with(['submissions' => function ($query) {
                $query->where('status', 'submitted')
                      ->whereDoesntHave('grade');
            }])
            ->get()
            ->pluck('submissions')
            ->flatten();
    }

    public function getTeachingCourses(int $instructorId): Collection
    {
        return $this->model->findOrFail($instructorId)
            ->courses()
            ->where('status', 'active')
            ->get();
    }
}
