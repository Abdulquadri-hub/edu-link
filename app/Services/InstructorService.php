<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Contracts\Services\InstructorServiceInterface;
use App\Contracts\Repositories\InstructorRepositoryInterface;

class InstructorService implements InstructorServiceInterface
{
    public function __construct(
        private InstructorRepositoryInterface $instructorRepo
    ) {}

    public function getAllInstructors()
    {
        return $this->instructorRepo->all();
    }

    public function getInstructorById(int $id)
    {
        return $this->instructorRepo->find($id);
    }

    public function createInstructor(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => Hash::make($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'user_type' => 'instructor',
                'status' => 'active',
            ]);

            return $this->instructorRepo->create([
                'user_id' => $user->id,
                'instructor_id' => $this->generateInstructorId(),
                'qualification' => $data['qualification'] ?? null,
                'specialization' => $data['specialization'] ?? null,
                'years_of_experience' => $data['years_of_experience'] ?? 0,
                'bio' => $data['bio'] ?? null,
                'linkedin_url' => $data['linkedin_url'] ?? null,
                'hourly_rate' => $data['hourly_rate'] ?? 0,
                'employment_type' => $data['employment_type'] ?? 'full-time',
                'hire_date' => $data['hire_date'] ?? now(),
                'status' => 'active',
            ]);
        });
    }

    public function updateInstructor(int $id, array $data)
    {
        return $this->instructorRepo->update($id, $data);
    }

    public function deleteInstructor(int $id)
    {
        return $this->instructorRepo->delete($id);
    }

    public function assignToCourse(int $instructorId, int $courseId)
    {
        $instructor = $this->instructorRepo->findOrFail($instructorId);
        $instructor->courses()->attach($courseId, [
            'assigned_date' => now(),
            'is_primary_instructor' => true,
        ]);
    }

    public function getInstructorDashboard(int $instructorId)
    {
        return [
            'instructor' => $this->instructorRepo->getWithCourses($instructorId),
            'upcoming_sessions' => $this->instructorRepo->getUpcomingSessions($instructorId),
            'pending_grading' => $this->instructorRepo->getPendingGrading($instructorId),
            'teaching_courses' => $this->instructorRepo->getTeachingCourses($instructorId),
            'monthly_hours' => $this->instructorRepo->getMonthlyHours($instructorId, now()->month, now()->year),
        ];
    }

    public function calculateMonthlyEarnings(int $instructorId)
    {
        $instructor = $this->instructorRepo->findOrFail($instructorId);
        return $instructor->calculateMonthlyEarnings(now()->month, now()->year);
    }

    private function generateInstructorId(): string
    {
        return 'INS' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}