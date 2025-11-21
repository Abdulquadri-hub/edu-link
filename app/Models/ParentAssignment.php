<?php

namespace App\Models;

use App\Models\Student;
use App\Models\ParentModel;
use Illuminate\Database\Eloquent\Model;
use App\Events\ParentAssignmentSubmitted;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentAssignment extends Model
{

    protected $fillable = [
        'parent_id',
        'student_id',
        'assignment_id',
        'submission_id',
        'parent_notes',
        'attachments',
        'status',
        'uploaded_at',
        'submitted_at',
    ];

    protected $casts = [
        'attachments' => 'array',
        'uploaded_at' => 'datetime',
        'submitted_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    // Helper Methods
    public function submitToInstructor(): bool
    {
        $submission = Submission::create([
            'assignment_id' => $this->assignment_id ?? null,
            'student_id' => $this->student_id,
            'content' => $this->parent_notes ?? 'Submitted by parent',
            'attachments' => $this->attachments,
            'submitted_at' => now(),
            'status' => 'submitted',
            'attempt_number' => 1,
            'is_late' => $this->assignment->due_at->isPast(),
        ]);

        $this->update([
            'submission_id' => $submission->id,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        event(new ParentAssignmentSubmitted($this));

        return true;
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded';
    }

    public function canEdit(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'submitted' => 'info',
            'graded' => 'success',
            default => 'gray',
        };
    }

    public function getSubmissionStatusText(): string
    {
        return match($this->status) {
            'pending' => 'Not yet submitted',
            'submitted' => 'Submitted to instructor',
            'graded' => 'Graded',
            default => 'Unknown',
        };
    }

}