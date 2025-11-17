<?php

namespace App\Contracts\Services;

interface CourseServiceInterface
{
    public function getAllCourses();
    public function getActiveCourses();
    public function getCourseById(int $id);
    public function createCourse(array $data);
    public function updateCourse(int $id, array $data);
    public function deleteCourse(int $id);
    public function getAvailableCourses();
    public function getCourseStatistics(int $courseId);
}
