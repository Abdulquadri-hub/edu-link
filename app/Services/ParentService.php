<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Contracts\Services\ParentServiceInterface;
use App\Contracts\Repositories\ParentRepositoryInterface;
use App\Contracts\Repositories\StudentRepositoryInterface;
use App\Contracts\Services\HashServiceInterface;

class ParentService implements ParentServiceInterface
{
    public function __construct(
        private ParentRepositoryInterface $parentRepo,
        private StudentRepositoryInterface $studentRepo,
        private HashServiceInterface $hashService
    ) {}

    public function getAllParents()
    {
        return $this->parentRepo->all();
    }

    public function getParentById(int $id)
    {
        return $this->parentRepo->find($id);
    }

    public function createParent(array $data)
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'email' => $data['email'],
                'username' => $data['username'],
                'password' => $this->hashService->make($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'phone' => $data['phone'] ?? null,
                'user_type' => 'parent',
                'status' => 'active',
            ]);

            return $this->parentRepo->create([
                'user_id' => $user->id,
                'parent_id' => $this->generateParentId(),
                'occupation' => $data['occupation'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'country' => $data['country'] ?? 'Nigeria',
                'secondary_phone' => $data['secondary_phone'] ?? null,
                'preferred_contact_method' => $data['preferred_contact_method'] ?? 'email',
                'receives_weekly_report' => $data['receives_weekly_report'] ?? true,
            ]);
        });
    }

    public function updateParent(int $id, array $data)
    {
        return $this->parentRepo->update($id, $data);
    }

    public function deleteParent(int $id)
    {
        return $this->parentRepo->delete($id);
    }

    public function linkChild(int $parentId, int $studentId, array $options)
    {
        $pivotData = [
            'relationship' => $options['relationship'] ?? 'guardian',
            'is_primary_contact' => $options['is_primary_contact'] ?? false,
            'can_view_grades' => $options['can_view_grades'] ?? true,
            'can_view_attendance' => $options['can_view_attendance'] ?? true,
        ];

        $this->parentRepo->linkChild($parentId, $studentId, $pivotData);
    }

    public function unlinkChild(int $parentId, int $studentId)
    {
        $this->parentRepo->unlinkChild($parentId, $studentId);
    }

    public function getParentDashboard(int $parentId)
    {
        return [
            'parent' => $this->parentRepo->getWithChildren($parentId),
            'children_progress' => $this->parentRepo->getChildrenProgress($parentId),
            'low_performing_children' => $this->parentRepo->getChildrenWithLowGrades($parentId),
        ];
    }

    public function getWeeklyReport(int $parentId)
    {
        $parent = $this->parentRepo->getWithChildren($parentId);
        $childrenProgress = $this->parentRepo->getChildrenProgress($parentId);

        return [
            'parent' => $parent,
            'week_period' => [
                'start' => now()->startOfWeek(),
                'end' => now()->endOfWeek(),
            ],
            'children' => $childrenProgress,
        ];
    }

    private function generateParentId(): string
    {
        return 'PAR' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
