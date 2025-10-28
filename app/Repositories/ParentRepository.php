<?php

namespace App\Repositories;

use App\Models\ParentModel;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Contracts\Repositories\ParentRepositoryInterface;

class ParentRepository extends BaseRepository implements ParentRepositoryInterface
{
    public function __construct(ParentModel $model)
    {
        parent::__construct($model);
    }

    public function findByParentId(string $parentId): ?Model
    {
        return $this->model->where('parent_id', $parentId)->first();
    }

    public function getWithChildren(int $parentId): ?Model
    {
        return $this->model->with('children.user')->find($parentId);
    }

    public function getChildrenProgress(int $parentId): array
    {
        $parent = $this->findOrFail($parentId);
        return $parent->getChildrenProgress();
    }

    public function linkChild(int $parentId, int $studentId, array $pivotData): void
    {
        $parent = $this->findOrFail($parentId);
        $parent->children()->attach($studentId, $pivotData);
    }

    public function unlinkChild(int $parentId, int $studentId): void
    {
        $parent = $this->findOrFail($parentId);
        $parent->children()->detach($studentId);
    }

    public function canViewChildGrades(int $parentId, int $studentId): bool
    {
        $parent = $this->findOrFail($parentId);
        return $parent->canViewChildGrades($studentId);
    }

    public function canViewChildAttendance(int $parentId, int $studentId): bool
    {
        $parent = $this->findOrFail($parentId);
        return $parent->canViewChildAttendance($studentId);
    }

    public function getChildrenWithLowGrades(int $parentId, float $threshold = 60): Collection
    {
        $parent = $this->findOrFail($parentId);
        return $parent->children()
            ->with(['enrollments' => function ($query) use ($threshold) {
                $query->where('final_grade', '<', $threshold)
                      ->whereNotNull('final_grade');
            }])
            ->get()
            ->filter(fn($child) => $child->enrollments->isNotEmpty());
    }
}
