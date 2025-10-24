<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Submission extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'assignment_id',
        'student_id',
        'content',
        'attachments',
        'submitted_at',
        'is_late',
        'status',
        'attempt_number',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
        'attempt_number' => 'integer',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function assignment(): BelongsTo {
        return $this->belongsTo(Assignment::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function grade(): HasOne {
        return $this->hasOne(Grade::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

      // Helper Methods
    public function updateProgress(): void
    {
        $totalAssignments = $this->course->assignments()->where('status', 'published')->count();
        
        if ($totalAssignments === 0) {
            $this->update(['progress_percentage' => 0]);
            return;
        }

        $completedAssignments = Submission::where('student_id', $this->student_id)
            ->whereHas('assignment', function ($query) {
                $query->where('course_id', $this->course_id)
                      ->where('status', 'published');
            })
            ->where('status', 'graded')
            ->count();

        $progress = round(($completedAssignments / $totalAssignments) * 100, 2);
        $this->update(['progress_percentage' => $progress]);
    }

    public function markCompleted(float $finalGrade): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_grade' => $finalGrade,
            'progress_percentage' => 100,
        ]);
    }
}
