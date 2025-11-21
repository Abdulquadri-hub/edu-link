<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Student extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'user_id', 'student_id', 'date_of_birth',
        'gender', 'address', 'city', ' state',
        'country', 'emergency_contact_name', 'emergency_contact_phone', 'enrollment_date',
        'enrollment_status', "notes", 'academic_level_id'
    ];

    protected $casts = [
        'enrollment_date' => 'datetime',
        'date_of_birth' => 'datetime',
        'academic_level_id' => 'integer',
    ];

    public function academicLevel(): BelongsTo {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }


    //students can have many parents
    public function parents(): BelongsToMany {
        return $this->belongsToMany(ParentModel::class,"student_parent", "student_id", "parent_id")->withPivot(['relationship', 'is_primary_contact', 'can_view_grades', 'can_view_attendance'])->withTimestamps();
    }

    public function primaryParent(): BelongsToMany {
        return $this->parents()->wherePivot('is_primary_contact', true);
    }

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany {
        return $this->enrollments()->where('status', 'active');
    }

     // NEW: Linking Requests
    public function linkingRequests(): HasMany
    {
        return $this->hasMany(ChildLinkingRequest::class, 'student_id');
    }

    public function pendingLinkingRequests(): HasMany
    {
        return $this->linkingRequests()->where('status', 'pending');
    }

    // NEW: Check if student has pending linking requests
    public function hasPendingLinkingRequests(): bool
    {
        return $this->pendingLinkingRequests()->exists();
    }

    // NEW: Get all parents (both confirmed and pending)
    public function getAllParentRelationships(): array
    {
        $confirmed = $this->parents()->get()->map(function ($parent) {
            return [
                'parent' => $parent,
                'status' => 'confirmed',
                'relationship' => $parent->pivot->relationship,
            ];
        });

        $pending = $this->linkingRequests()->pending()->with('parent.user')->get()->map(function ($request) {
            return [
                'parent' => $request->parent,
                'status' => 'pending',
                'relationship' => $request->relationship,
            ];
        });

        return $confirmed->merge($pending)->toArray();
    }

    // this is same as courses enrolled to 
    public function courses(): BelongsToMany {
        return $this->belongsToMany(Course::class, "enrollments")->withPivot(['enrolled_at', 'status', 'progress_percentage', 'final_grade'])->withTimestamps();
    }

    public function submissions(): HasMany {
        return $this->hasMany(Submission::class);
    }

    public function attendances(): HasMany {
        return $this->hasMany(Attendance::class);
    }

    // public function counselingSessions(): HasMany {
    //     return $this->hasMany(CounsellingSession::class);
    // }

    public function grades(): HasManyThrough {
        return $this->hasManyThrough(Grade::class, Submission::class);
    }

    // accessors() and mutators

    public function getAgeAttribute(): int {
        return $this->date_of_birth->age ?? 0;
    }

    public function getFullAddressAttribute(): string {
        return trim("{$this->address}, {$this->city}, {$this->state}, {$this->country}");
    }

    public function getGradeLevelAttribute(): ?string {
        return $this->academicLevel?->name;
    }


    //scopes

    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function scopeGraduated($query) {
        $query->where('enrollment_status', 'graduated');
    }

    public function scopeByGradeLevel($query, int $levelId) {
        return $query->where('academic_level_id', $levelId);
    }
    
    //helpers methods 
    
    public function calculateOverallProgress(): float {
        return $this->activeEnrollments()->avg('progress_percentage') ?? 0.00;
    }

    public function calculateAttendanceRate(): float {
        $total = $this->attendances()->count();
        if($total === 0); return 0;

        $present = $this->attendances()->where('status', 'present');
        return round(($present / $total) * 100 , 2); 
    }

    public function hasParent($parentId) {
        return $this->parents()->where('parent_id', $parentId)->exists();
    }

    public function getCurrentGradeNumber(): ?int
    {
        return $this->academicLevel?->grade_number;
    }

    public function isInElementary(): bool
    {
        return $this->academicLevel?->isElementary() ?? false;
    }

    public function isInMiddle(): bool
    {
        return $this->academicLevel?->isMiddle() ?? false;
    }

    public function isInHigh(): bool
    {
        return $this->academicLevel?->isHigh() ?? false;
    }

    public function canEnrollInCourse(Course $course): bool
    {
        // Check if course is for student's grade level
        if ($course->academic_level_id && $this->academic_level_id) {
            return $course->academic_level_id === $this->academic_level_id;
        }
        
        return true; // Allow enrollment if no level restrictions
    }

    public function getRecommendedCourses()
    {
        return Course::where('academic_level_id', $this->academic_level_id)
                     ->where('status', 'active')
                     ->whereDoesntHave('enrollments', function ($query) {
                         $query->where('student_id', $this->id)
                               ->where('status', 'active');
                     })
                     ->get();
    }

    public function enrollmentRequests(): HasMany
    {
        return $this->hasMany(EnrollmentRequest::class);
    }

    public function pendingEnrollmentRequests(): HasMany
    {
        return $this->enrollmentRequests()->whereIn('status', ['pending', 'parent_notified', 'payment_pending']);
    }

    public function getPendingEnrollmentRequestsCount(): int
    {
        return $this->pendingEnrollmentRequests()->count();
    }

    public function hasPendingRequestFor(int $courseId): bool
    {
        return $this->enrollmentRequests()
            ->where('course_id', $courseId)
            ->whereIn('status', ['pending', 'parent_notified', 'payment_pending'])
            ->exists();
    }

     // NEW: Student Promotions
    public function promotions(): HasMany
    {
        return $this->hasMany(StudentPromotion::class);
    }

    public function completedPromotions(): HasMany
    {
        return $this->promotions()->where('status', 'completed');
    }

    public function pendingPromotions(): HasMany
    {
        return $this->promotions()->where('status', 'pending');
    }

    public function latestPromotion(): HasOne
    {
        return $this->hasOne(StudentPromotion::class)->latestOfMany('promotion_date');
    }

    // NEW: Get promotion history
    public function getPromotionHistory(): array
    {
        return $this->promotions()
            ->with(['fromLevel', 'toLevel', 'promoter'])
            ->orderBy('promotion_date', 'desc')
            ->get()
            ->map(function ($promotion) {
                return [
                    'code' => $promotion->promotion_code,
                    'from' => $promotion->fromLevel?->name,
                    'to' => $promotion->toLevel->name,
                    'type' => $promotion->promotion_type,
                    'date' => $promotion->promotion_date,
                    'status' => $promotion->status,
                    'promoted_by' => $promotion->promoter->full_name,
                ];
            })
            ->toArray();
    }

    // NEW: Check if student has pending promotion
    public function hasPendingPromotion(): bool
    {
        return $this->pendingPromotions()->exists();
    }

    // NEW: Promote student
    public function promote(
        int $toLevelId, 
        int $promotedBy,
        string $type = 'regular',
        ?string $academicYear = null,
        ?float $finalGpa = null,
        ?string $notes = null,
        bool $autoUpdateEnrollments = true
    ): StudentPromotion {
        return StudentPromotion::create([
            'student_id' => $this->id,
            'from_level_id' => $this->academic_level_id,
            'to_level_id' => $toLevelId,
            'promotion_type' => $type,
            'academic_year' => $academicYear,
            'final_gpa' => $finalGpa,
            'promotion_notes' => $notes,
            'promoted_by' => $promotedBy,
            'status' => 'pending',
            'auto_update_enrollments' => $autoUpdateEnrollments,
        ]);
    }

    // NEW: Get next grade level
    public function getNextGradeLevel(): ?AcademicLevel
    {
        return $this->academicLevel?->getNextLevel();
    }

    // NEW: Can be promoted
    public function canBePromoted(): array
    {
        if (!$this->academic_level_id) {
            return ['can_promote' => false, 'reason' => 'No current grade level assigned'];
        }

        if ($this->hasPendingPromotion()) {
            return ['can_promote' => false, 'reason' => 'Promotion already pending'];
        }

        $nextLevel = $this->getNextGradeLevel();
        if (!$nextLevel) {
            return ['can_promote' => false, 'reason' => 'Already at highest grade level'];
        }

        if ($this->enrollment_status !== 'active') {
            return ['can_promote' => false, 'reason' => 'Student is not active'];
        }

        return ['can_promote' => true, 'reason' => null, 'next_level' => $nextLevel];
    }

}
