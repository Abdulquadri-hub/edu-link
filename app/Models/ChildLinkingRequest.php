<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildLinkingRequest extends Model
{
    protected $fillable = [
        'parent_id',
        'student_id',
        'relationship',
        'is_primary_contact',
        'can_view_grades',
        'can_view_attendance',
        'parent_message',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'can_view_grades' => 'boolean',
        'can_view_attendance' => 'boolean',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // Helper Methods
    public function approve(int $adminUserId, ?string $notes = null): bool
    {
        // Check if already linked
        if ($this->parent->children()->where('student_parent.student_id', $this->student_id)->exists()) {
            $this->update([
                'status' => 'rejected',
                'admin_notes' => 'Already linked',
                'reviewed_by' => $adminUserId,
                'reviewed_at' => now(),
            ]);
            return false;
        }

        // Create the link in student_parent pivot table
        $this->parent->children()->attach($this->student_id, [
            'relationship' => $this->relationship,
            'is_primary_contact' => $this->is_primary_contact,
            'can_view_grades' => $this->can_view_grades,
            'can_view_attendance' => $this->can_view_attendance,
        ]);

        // Update request status
        $this->update([
            'status' => 'approved',
            'admin_notes' => $notes,
            'reviewed_by' => $adminUserId,
            'reviewed_at' => now(),
        ]);

        // TODO: Send notification to parent
        // event(new ChildLinkingApproved($this));

        return true;
    }

    public function reject(int $adminUserId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'reviewed_by' => $adminUserId,
            'reviewed_at' => now(),
        ]);

        // TODO: Send notification to parent
        // event(new ChildLinkingRejected($this));
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeReviewed(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Awaiting Admin Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }
}