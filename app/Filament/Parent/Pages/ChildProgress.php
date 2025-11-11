<?php

namespace App\Filament\Parent\Pages;

use UnitEnum;
use BackedEnum;
use App\Models\Student;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;
use App\Contracts\Services\ParentServiceInterface;

class ChildProgress extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Progress Report';
    protected static string|UnitEnum|null $navigationGroup = 'Reports';
    protected string $view = 'filament.parent.pages.child-progress';
    
    public $child;

    public function mount(): void
    {
        $childId = request()->query('child');
        
        if ($childId) {
            $this->child = Student::whereHas('parents', function ($query) {
                $query->where('parent_id', Auth::user()->parent->id);
            })->findOrFail($childId);
        }
    }

    public function getTitle(): string | Htmlable
    {
        return $this->child 
            ? 'Progress Report - ' . $this->child->user->full_name 
            : 'Select a Child';
    }

    protected function getViewData(): array
    {
        if (!$this->child) {
            return ['children' => Auth::user()->parent->children];
        }

        $parent = Auth::user()->parent;
        
        return [
            'child' => $this->child,
            'progress' => $this->child->calculateOverallProgress(),
            'attendance' => $this->child->calculateAttendanceRate(),
            'courses' => $this->child->activeEnrollments()->with('course')->get(),
            'recent_grades' => $this->child->grades()
                ->where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}
