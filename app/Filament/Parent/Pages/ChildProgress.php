<?php

namespace App\Filament\Parent\Pages;

use UnitEnum;
use BackedEnum;
use App\Models\Student;
use Filament\Pages\Page;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Tables\Columns\ProgressColumn;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Concerns\InteractsWithTable;

class ChildProgress extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Children Progress';
    protected static string|UnitEnum|null $navigationGroup = 'Monitoring';
    protected string $view = 'filament.parent.pages.child-progress';
    public $child;
    
    public function mount(): void
    {
        // Check if parent has children
        $parent = Auth::user()->parent;
        if (!$parent || $parent->children()->count() === 0) {
            redirect()->route('filament.parent.pages.dashboard')
                ->with('warning', 'You have no linked children yet.');
        }

        $childId = request()->query('child');
        
        if ($childId) {
            $this->child = Student::whereHas('parents', function ($query) {
                $query->where('student_parent.parent_id', Auth::user()->parent->id);
            })->findOrFail($childId);
        }
    }

    public function getTitle(): string | Htmlable
    {
        return $this->child 
            ? 'Progress Report - ' . $this->child->user->full_name 
            : 'Select a Child';
    }

    /**
     * Get all children for the authenticated parent
     */
    public function getChildren()
    {
        return Auth::user()->parent->children()
            ->with(['user', 'enrollments.course', 'attendances', 'grades'])
            ->get();
    }

    /**
     * Get detailed progress for a specific child
     */
    public function getChildProgress(Student $child): array
    {
        return [
            'student' => $child,
            'overall_progress' => $child->calculateOverallProgress(),
            'attendance_rate' => $child->calculateAttendanceRate(),
            'enrollments' => $child->enrollments()
                ->with(['course', 'student.grades'])
                ->where('status', 'active')
                ->get(),
            'recent_grades' => $child->grades()
                ->where('is_published', true)
                ->orderBy('published_at', 'desc')
                ->limit(5)
                ->get(),
            'upcoming_classes' => $child->courses()
                ->with(['classSessions' => function ($query) {
                    $query->where('scheduled_at', '>', now())
                        ->where('status', 'scheduled')
                        ->orderBy('scheduled_at')
                        ->limit(5);
                }])
                ->get()
                ->pluck('classSessions')
                ->flatten(),
        ];
    }

    /**
     * Get summary statistics for all children
     */
    public function getChildrenSummary(): array
    {
        $children = $this->getChildren();
        
        return [
            'total_children' => $children->count(),
            'average_grade' => $children->map(function ($child) {
                $avgGrade = $child->grades()
                    ->where('is_published', true)
                    ->avg('percentage');
                return $avgGrade ?? 0;
            })->avg(),
            'average_attendance' => $children->map(fn($child) => $child->calculateAttendanceRate())->avg(),
            'total_courses' => $children->sum(fn($child) => $child->activeEnrollments()->count()),
            'low_performing' => $children->filter(function ($child) {
                $avg = $child->grades()->where('is_published', true)->avg('percentage');
                return $avg && $avg < 60;
            })->count(),
        ];
    }

    /**
     * Table for listing all children with quick stats
     */
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Student::query()
                    ->whereHas('parents', function ($query) {
                        $query->where('student_parent.parent_id', Auth::user()->parent->id);
                    })
                    ->with(['user', 'enrollments', 'attendances', 'grades'])
            )
            ->columns([
                TextColumn::make('student_id')
                    ->label('Student ID')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('user.full_name')
                    ->label('Name')
                    ->searchable(['first_name', 'last_name'])
                    ->sortable()
                    ->description(fn ($record) => $record->user->email),
                
                TextColumn::make('active_courses')
                    ->label('Courses')
                    ->getStateUsing(fn ($record) => $record->activeEnrollments()->count())
                    ->badge()
                    ->color('info'),
                
                TextColumn::make('average_grade')
                    ->label('Avg Grade')
                    ->getStateUsing(function ($record) {
                        $avg = $record->grades()->where('is_published', true)->avg('percentage');
                        return $avg ? round($avg, 1) . '%' : 'N/A';
                    })
                    ->color(fn ($state) => match(true) {
                        $state === 'N/A' => 'gray',
                        floatval($state) >= 90 => 'success',
                        floatval($state) >= 80 => 'info',
                        floatval($state) >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->weight('bold'),
                
                TextColumn::make('attendance_rate')
                    ->label('Attendance')
                    ->getStateUsing(fn ($record) => $record->calculateAttendanceRate())
                    ->color(fn ($state) => match(true) {
                        $state >= 85 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger',
                    }),
                
                TextColumn::make('enrollment_status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'info' => 'graduated',
                        'warning' => 'dropped',
                        'danger' => 'suspended',
                    ]),
            ])
            ->recordActions([
                // Actions will be added in the Blade view
            ])
            ->paginated(false);
    }

    /**
     * Get view data to pass to Blade
     */
    public function getViewData(): array
    {
        return [
            'children' => $this->getChildren(),
            'summary' => $this->getChildrenSummary(),
        ];
    }
}
