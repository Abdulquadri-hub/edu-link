<?php

namespace App\Contracts\Services;

interface StudentServiceInterface
{
    public function getAllStudents();
    public function getStudentById(int $id);
    public function createStudent(array $data);
    public function updateStudent(int $id, array $data);
    public function deleteStudent(int $id);
    public function enrollInCourse(int $studentId, int $courseId);
    public function getStudentProgress(int $studentId);
    public function getStudentDashboard(int $studentId);
    public function getUpcomingClasses(int $studentId);
}
