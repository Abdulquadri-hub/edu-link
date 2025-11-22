<?php

namespace App\Services;

use App\Models\User;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Contracts\Services\StudentServiceInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Repositories\EnrollmentRepositoryInterface;

class StudentService implements StudentServiceInterface
{
    public function __construct(
        private StudentRepositoryInterface $studentRepo,
        private EnrollmentRepositoryInterface $enrollmentRepo
    ) {}

    public function getAllStudents()
    {
        return $this->studentRepo->all();
    }

    public function getStudentById(int $id)
    {
        return $this->studentRepo->find($id);
    }

    public function createStudent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'user_type' => 'student',
                'status' => $data['status'] ?? 'active', // Use provided status or default to active
                'email_verified_at' => $data['email_verified_at'] ?? now(), // Use provided status or default to verified
            ]);

            // Create student profile
            $student = $this->studentRepo->create([
                'user_id' => $user->id,
                'student_id' => $this->generateStudentId(),
                'date_of_birth' => $data['date_of_birth'],
                'gender' => $data['gender'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'Nigeria',
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'enrollment_date' => now(),
                'enrollment_status' => $data['enrollment_status'] ?? 'active', // Use provided status or default to active
            ]);
            
            return $student;
        });
    }

    public function updateStudent(int $id, array $data)
    {
        return $this->studentRepo->update($id, $data);
    }

    public function deleteStudent(int $id)
    {
        return $this->studentRepo->delete($id);
    }

    public function enrollInCourse(int $studentId, int $courseId)
    {
        return $this->enrollmentRepo->enroll($studentId, $courseId);
    }

    public function getStudentProgress(int $studentId)
    {
        return [
            'overall_progress' => $this->studentRepo->calculateOverallProgress($studentId),
            'attendance_rate' => $this->studentRepo->calculateAttendanceRate($studentId),
            'enrollments' => $this->enrollmentRepo->getByStudent($studentId),
        ];
    }

    public function getStudentDashboard(int $studentId) {
        return [
            'student' => $this->studentRepo->find($studentId),
            'upcoming_classes' => $this->studentRepo->getUpcomingClasses($studentId),
            'pending_assignments' => $this->studentRepo->getPendingAssignments($studentId),
            'recent_grades' => $this->studentRepo->getRecentGrades($studentId, 5),
            'progress' => $this->getStudentProgress($studentId),
        ];
    }

    public function getUpcomingClasses(int $studentId)
    {
        return $this->studentRepo->getUpcomingClasses($studentId);
    }

    private function generateStudentId(): string {
        return 'STU' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }

}