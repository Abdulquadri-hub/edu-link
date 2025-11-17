<?php

namespace App\Services;


use App\Contracts\Services\CourseServiceInterface;
use App\Contracts\Repositories\CourseRepositoryInterface;

class CourseService implements CourseServiceInterface
{
    public function __construct(
        private CourseRepositoryInterface $courseRepo
    ) {}

    public function getAllCourses()
    {
        return $this->courseRepo->all();
    }

    public function getActiveCourses()
    {
        return $this->courseRepo->getAllActive();
    }

    public function getCourseById(int $id)
    {
        return $this->courseRepo->find($id);
    }

    public function createCourse(array $data)
    {
        return $this->courseRepo->create([
            'course_code' => $data['course_code'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'category' => $data['category'] ?? 'academic',
            'level' => $data['level'] ?? 'beginner',
            'duration_weeks' => $data['duration_weeks'] ?? 12,
            'credit_hours' => $data['credit_hours'] ?? 3,
            'price' => $data['price'] ?? 0,
            'thumbnail' => $data['thumbnail'] ?? null,
            'learning_objectives' => $data['learning_objectives'] ?? null,
            'prerequisites' => $data['prerequisites'] ?? null,
            'status' => $data['status'] ?? 'draft',
            'max_students' => $data['max_students'] ?? null,
        ]);
    }

    public function updateCourse(int $id, array $data)
    {
        return $this->courseRepo->update($id, $data);
    }

    public function deleteCourse(int $id)
    {
        return $this->courseRepo->delete($id);
    }

    public function getAvailableCourses()
    {
        return $this->courseRepo->getAvailableCourses();
    }

    public function getCourseStatistics(int $courseId)
    {
        $course = $this->courseRepo->getWithInstructors($courseId);
        
        return [
            'course' => $course,
            'enrolled_count' => $course->getEnrolledCount(),
            'completion_rate' => $course->getCompletionRate(),
            'average_grade' => $course->getAverageGrade(),
            'is_full' => $course->isFull(),
        ];
    }
}
