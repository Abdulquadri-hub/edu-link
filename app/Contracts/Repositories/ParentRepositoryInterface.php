<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface ParentRepositoryInterface extends BaseRepositoryInterface
{
    public function findByParentId(string $parentId): ?Model;
    public function getWithChildren(int $parentId): ?Model;
    public function getChildrenProgress(int $parentId): array;
    public function linkChild(int $parentId, int $studentId, array $pivotData): void;
    public function unlinkChild(int $parentId, int $studentId): void;
    public function canViewChildGrades(int $parentId, int $studentId): bool;
    public function canViewChildAttendance(int $parentId, int $studentId): bool;
    public function getChildrenWithLowGrades(int $parentId, float $threshold = 60): Collection;
}