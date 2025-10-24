<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assignment extends Model
{
    use SoftDeletes;

       protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'description',
        'instructions',
        'assigned_at',
        'due_at',
        'max_score',
        'type',
        'allows_late_submission',
        'late_penalty_percentage',
        'attachments',
        'status',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'max_score' => 'integer',
        'allows_late_submission' => 'boolean',
        'late_penalty_percentage' => 'integer',
        'attachments' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(Instructor::class);
    }

    public function submissions(): HasMany {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasManyThrough {
        return $this->hasManyThrough(Grade::class, Submission::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
                    ->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('due_at', '>', now())
                    ->where('status', 'published');
    }

    // Helper Methods
    public function isOverdue(): bool
    {
        return $this->due_at->isPast();
    }

    public function getDaysUntilDue(): int
    {
        return $this->due_at->diffInDays(now(), false);
    }

    public function getSubmissionCount(): int
    {
        return $this->submissions()->count();
    }

    public function getGradedCount(): int
    {
        return $this->submissions()->where('status', 'graded')->count();
    }

    public function getAverageScore(): float
    {
        return $this->grades()
            ->where('is_published', true)
            ->avg('percentage') ?? 0;
    }

    public function hasStudentSubmitted(int $studentId): bool
    {
        return $this->submissions()
            ->where('student_id', $studentId)
            ->exists();
    }

}
