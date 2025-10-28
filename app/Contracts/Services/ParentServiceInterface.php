<?php

namespace App\Contracts\Services;

use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Collection;

interface ParentServiceInterface
{
    public function getAllParents();
    public function getParentById(int $id);
    public function createParent(array $data);
    public function updateParent(int $id, array $data);
    public function deleteParent(int $id);
    public function linkChild(int $parentId, int $studentId, array $options);
    public function unlinkChild(int $parentId, int $studentId);
    public function getParentDashboard(int $parentId);
    public function getWeeklyReport(int $parentId);
}
