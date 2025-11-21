<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ParentModel extends Model
{
    use SoftDeletes;

    protected $table = 'parents';
    
    protected $fillable = [
        'user_id', 'parent_id', 'occupation',
        'address', 'city', 'state', 'country',
        'secondary_phone', 'preferred_contact_method',
        'recieves_weekly_report'
    ];

    protected $casts = [
        "recieves_weekly_report" => 'boolean'
    ];
    
    // Relationships
    
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function children(): BelongsToMany {
        return $this->belongsToMany(Student::class, "student_parent", "parent_id", "student_id")
            ->withPivot(["relationship", "is_primary_contact", "can_view_grades", "can_view_attendance"])
            ->withTimestamps();
    }

    public function primaryChildren(): BelongsToMany {
        return $this->children()->wherePivot('is_primary_contact', true);
    }

    public function reports(): HasMany {
        return $this->hasMany(Report::class, "parent_id");
    }

    // NEW: Parent Assignments relationship
    public function parentAssignments(): HasMany {
        return $this->hasMany(ParentAssignment::class, 'parent_id');
    }

    public function pendingAssignments(): HasMany {
        return $this->parentAssignments()->where('status', 'pending');
    }

    // NEW: Child Linking Requests
    public function linkingRequests(): HasMany
    {
        return $this->hasMany(ChildLinkingRequest::class, 'parent_id');
    }

    public function pendingLinkingRequests(): HasMany
    {
        return $this->linkingRequests()->where('status', 'pending');
    }

    public function approvedLinkingRequests(): HasMany
    {
        return $this->linkingRequests()->where('status', 'approved');
    }

    // NEW: Helper to check if linking request exists
    public function hasLinkingRequestFor(int $studentId): bool
    {
        return $this->linkingRequests()
            ->where('student_id', $studentId)
            ->where('status', 'pending')
            ->exists();
    }

    // NEW: Get pending requests count
    public function getPendingLinkingRequestsCount(): int
    {
        return $this->pendingLinkingRequests()->count();
    }


    // Accessors / Mutators

    public function getFullAddressAttribute(): string {
        return trim("{$this->address}, {$this->city}, {$this->state}, {$this->country}");
    }
    
    // Helpers

    public function canViewChildGrades(int $childId): bool {
        return $this->children()
            ->where('student_parent.student_id', $childId)
            ->wherePivot('can_view_grades', true)
            ->exists();
    }

    public function canViewChildAttendance(int $childId): bool {
        return $this->children()
            ->where('student_parent.student_id', $childId)
            ->wherePivot('can_view_attendance', true)
            ->exists();
    }

    public function getChildrenProgress(): array {
        return $this->children->map(function ($child) {
            return [
                "student" => $child->student_id,
                "name" => $child->user->full_name,
                "progress" => $child->calculateOverallProgress(),
                "attendance_rate" => $child->calculateAttendanceRate()
            ];
        })->toArray();
    }

    // NEW: Get pending assignments count
    public function getPendingAssignmentsCount(): int {
        return $this->pendingAssignments()->count();
    }

    // NEW: Get assignments for specific child
    public function getChildAssignments(int $studentId) {
        return $this->parentAssignments()
            ->where('student_id', $studentId)
            ->with(['assignment.course', 'submission.grade'])
            ->get();
    }
}