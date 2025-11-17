<?php

namespace App\Contracts\Services;

interface InstructorServiceInterface
{
    public function getAllInstructors();
    public function getInstructorById(int $id);
    public function createInstructor(array $data);
    public function updateInstructor(int $id, array $data);
    public function deleteInstructor(int $id);
    public function assignToCourse(int $instructorId, int $courseId);
    public function getInstructorDashboard(int $instructorId);
    public function calculateMonthlyEarnings(int $instructorId);
}
