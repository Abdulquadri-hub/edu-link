<?php

namespace App\Repositories;

use App\Contracts\Repositories\CourseRepositoryInterface;
use App\Models\Course;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class CourseRepository extends BaseRepository implements CourseRepositoryInterface
{
    public function __construct(Course $model)
    {
        parent::__construct($model);
    }

    public function findByCourseCode(string $courseCode): ?Model
    {
        return $this->model->where('course_code', $courseCode)->first();
    }

    public function getAllActive(): Collection
    {
        return $this->model->active()->get();
    }

    public function getByCategory(string $category): Collection
    {
        return $this->model->byCategory($category)->get();
    }

    public function getByLevel(string $level): Collection
    {
        return $this->model->byLevel($level)->get();
    }

    public function getWithInstructors(int $courseId): ?Model
    {
        return $this->model->with('instructors.user')->find($courseId);
    }

    public function getEnrolledStudents(int $courseId): Collection
    {
        return $this->model->findOrFail($courseId)
            ->students()
            ->with('user')
            ->get();
    }

    public function getAvailableCourses(): Collection
    {
        return $this->model->active()
            ->where(function ($query) {
                $query->whereNull('max_students')
                      ->orWhereRaw('(SELECT COUNT(*) FROM enrollments WHERE course_id = courses.id AND status = "active") < max_students');
            })
            ->get();
    }

    public function isFull(int $courseId): bool
    {
        $course = $this->findOrFail($courseId);
        return $course->isFull();
    }

    public function getCompletionRate(int $courseId): float
    {
        $course = $this->findOrFail($courseId);
        return $course->getCompletionRate();
    }
}

