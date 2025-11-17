<?php

/**
 * ==========================================
 * EDULINK ELOQUENT MODELS
 * Complete Model Classes for Laravel 12
 * ==========================================
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

// ============================================
// 1. USER MODEL (Base Authentication)
// ============================================
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, LogsActivity;

    protected $fillable = [
        'email',
        'username',
        'password',
        'first_name',
        'last_name',
        'phone',
        'avatar',
        'user_type',
        'status',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Activity Log Configuration
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['email', 'first_name', 'last_name', 'status'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    // Relationships
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }

    public function parent(): HasOne
    {
        return $this->hasOne(ParentModel::class);
    }

    public function instructor(): HasOne
    {
        return $this->hasOne(Instructor::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }

    public function generatedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    // Accessors & Mutators
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAvatarUrlAttribute(): string
    {
        return $this->avatar 
            ? asset('storage/' . $this->avatar) 
            : asset('images/default-avatar.png');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('user_type', $type);
    }

    // Helper Methods
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->user_type === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->user_type === 'student';
    }

    public function isParent(): bool
    {
        return $this->user_type === 'parent';
    }
}

// ============================================
// 2. STUDENT MODEL
// ============================================
class Student extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'student_id',
        'date_of_birth',
        'gender',
        'address',
        'city',
        'state',
        'country',
        'emergency_contact_name',
        'emergency_contact_phone',
        'enrollment_date',
        'enrollment_status',
        'notes',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'enrollment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['enrollment_status', 'emergency_contact_phone'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parents(): BelongsToMany
    {
        return $this->belongsToMany(ParentModel::class, 'student_parent', 'student_id', 'parent_id')
            ->withPivot(['relationship', 'is_primary_contact', 'can_view_grades', 'can_view_attendance'])
            ->withTimestamps();
    }

    public function primaryParent(): BelongsToMany
    {
        return $this->parents()->wherePivot('is_primary_contact', true);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'enrollments')
            ->withPivot(['enrolled_at', 'status', 'progress_percentage', 'final_grade'])
            ->withTimestamps();
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function counselingSessions(): HasMany
    {
        return $this->hasMany(CounselingSession::class);
    }

    public function grades(): HasMany
    {
        return $this->hasManyThrough(Grade::class, Submission::class);
    }

    // Accessors
    public function getAgeAttribute(): int
    {
        return $this->date_of_birth->age ?? 0;
    }

    public function getFullAddressAttribute(): string
    {
        return trim("{$this->address}, {$this->city}, {$this->state}, {$this->country}");
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeGraduated($query)
    {
        return $query->where('enrollment_status', 'graduated');
    }

    // Helper Methods
    public function calculateOverallProgress(): float
    {
        return $this->activeEnrollments()->avg('progress_percentage') ?? 0;
    }

    public function calculateAttendanceRate(): float
    {
        $total = $this->attendances()->count();
        if ($total === 0) return 0;

        $present = $this->attendances()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    public function hasParent(int $parentId): bool
    {
        return $this->parents()->where('parent_id', $parentId)->exists();
    }
}

// ============================================
// 3. PARENT MODEL
// ============================================
class ParentModel extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'parent_id',
        'occupation',
        'address',
        'city',
        'state',
        'country',
        'secondary_phone',
        'preferred_contact_method',
        'receives_weekly_report',
    ];

    protected $casts = [
        'receives_weekly_report' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['preferred_contact_method', 'receives_weekly_report'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function children(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_parent', 'parent_id', 'student_id')
            ->withPivot(['relationship', 'is_primary_contact', 'can_view_grades', 'can_view_attendance'])
            ->withTimestamps();
    }

    public function primaryChildren(): BelongsToMany
    {
        return $this->children()->wherePivot('is_primary_contact', true);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'parent_id');
    }

    // Accessors
    public function getFullAddressAttribute(): string
    {
        return trim("{$this->address}, {$this->city}, {$this->state}, {$this->country}");
    }

    // Helper Methods
    public function canViewChildGrades(int $studentId): bool
    {
        return $this->children()
            ->where('student_id', $studentId)
            ->wherePivot('can_view_grades', true)
            ->exists();
    }

    public function canViewChildAttendance(int $studentId): bool
    {
        return $this->children()
            ->where('student_id', $studentId)
            ->wherePivot('can_view_attendance', true)
            ->exists();
    }

    public function getChildrenProgress(): array
    {
        return $this->children->map(function ($child) {
            return [
                'student_id' => $child->student_id,
                'name' => $child->user->full_name,
                'progress' => $child->calculateOverallProgress(),
                'attendance_rate' => $child->calculateAttendanceRate(),
            ];
        })->toArray();
    }
}

// ============================================
// 4. INSTRUCTOR MODEL
// ============================================
class Instructor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'instructor_id',
        'qualification',
        'specialization',
        'years_of_experience',
        'bio',
        'linkedin_url',
        'hourly_rate',
        'employment_type',
        'hire_date',
        'status',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'hourly_rate' => 'decimal:2',
        'years_of_experience' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'hourly_rate', 'employment_type'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'instructor_course')
            ->withPivot(['assigned_date', 'is_primary_instructor'])
            ->withTimestamps();
    }

    public function primaryCourses(): BelongsToMany
    {
        return $this->courses()->wherePivot('is_primary_instructor', true);
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function completedSessions(): HasMany
    {
        return $this->classSessions()->where('status', 'completed');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function grades(): HasMany
    {
        return $this->hasMany(Grade::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    public function counselingSessions(): HasMany
    {
        return $this->hasMany(CounselingSession::class, 'counselor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFullTime($query)
    {
        return $query->where('employment_type', 'full-time');
    }

    // Helper Methods
    public function calculateMonthlyHours(int $month = null, int $year = null): float
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        $totalMinutes = $this->completedSessions()
            ->whereMonth('started_at', $month)
            ->whereYear('started_at', $year)
            ->sum('duration_minutes');

        return round($totalMinutes / 60, 2);
    }

    public function calculateMonthlyEarnings(int $month = null, int $year = null): float
    {
        $hours = $this->calculateMonthlyHours($month, $year);
        return round($hours * $this->hourly_rate, 2);
    }

    public function getStudentCount(): int
    {
        return Student::whereHas('enrollments.course.instructors', function ($query) {
            $query->where('instructor_id', $this->id);
        })->distinct()->count();
    }

    public function isTeaching(int $courseId): bool
    {
        return $this->courses()->where('course_id', $courseId)->exists();
    }
}

// ============================================
// 5. COURSE MODEL
// ============================================
class Course extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'course_code',
        'title',
        'description',
        'category',
        'level',
        'duration_weeks',
        'credit_hours',
        'price',
        'thumbnail',
        'learning_objectives',
        'prerequisites',
        'status',
        'max_students',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_weeks' => 'integer',
        'credit_hours' => 'integer',
        'max_students' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'price'])
            ->logOnlyDirty();
    }

    // Relationships
    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(Instructor::class, 'instructor_course')
            ->withPivot(['assigned_date', 'is_primary_instructor'])
            ->withTimestamps();
    }

    public function primaryInstructor(): BelongsToMany
    {
        return $this->instructors()->wherePivot('is_primary_instructor', true);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'enrollments')
            ->withPivot(['enrolled_at', 'status', 'progress_percentage', 'final_grade'])
            ->withTimestamps();
    }

    public function classSessions(): HasMany
    {
        return $this->hasMany(ClassSession::class);
    }

    public function upcomingSessions(): HasMany
    {
        return $this->classSessions()
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    // Accessors
    public function getThumbnailUrlAttribute(): string
    {
        return $this->thumbnail 
            ? asset('storage/' . $this->thumbnail) 
            : asset('images/default-course.png');
    }

    public function getLearningObjectivesArrayAttribute(): array
    {
        return $this->learning_objectives 
            ? json_decode($this->learning_objectives, true) 
            : [];
    }

    public function getPrerequisitesArrayAttribute(): array
    {
        return $this->prerequisites 
            ? json_decode($this->prerequisites, true) 
            : [];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByLevel($query, string $level)
    {
        return $query->where('level', $level);
    }

    // Helper Methods
    public function isFull(): bool
    {
        if (!$this->max_students) return false;
        
        return $this->activeEnrollments()->count() >= $this->max_students;
    }

    public function hasAvailableSlots(): bool
    {
        return !$this->isFull();
    }

    public function getEnrolledCount(): int
    {
        return $this->activeEnrollments()->count();
    }

    public function getCompletionRate(): float
    {
        $completed = $this->enrollments()->where('status', 'completed')->count();
        $total = $this->enrollments()->count();

        return $total > 0 ? round(($completed / $total) * 100, 2) : 0;
    }

    public function getAverageGrade(): float
    {
        return $this->enrollments()
            ->whereNotNull('final_grade')
            ->avg('final_grade') ?? 0;
    }
}

// ============================================
// 6. ENROLLMENT MODEL
// ============================================
class Enrollment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'student_id',
        'course_id',
        'enrolled_at',
        'completed_at',
        'status',
        'progress_percentage',
        'final_grade',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'date',
        'completed_at' => 'date',
        'progress_percentage' => 'decimal:2',
        'final_grade' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'progress_percentage', 'final_grade'])
            ->logOnlyDirty();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }

    // Scopes
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    // Helper Methods
    public function checkIfLate(): void
    {
        $isLate = $this->submitted_at->isAfter($this->assignment->due_at);
        $this->update(['is_late' => $isLate]);
    }

    public function calculatePenalty(): float
    {
        if (!$this->is_late || !$this->assignment->allows_late_submission) {
            return 0;
        }

        return $this->assignment->late_penalty_percentage;
    }

    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->grade !== null;
    }
}

// ============================================
// 7. CLASS SESSION MODEL
// ============================================
class ClassSession extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'description',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_minutes',
        'google_meet_link',
        'google_calendar_event_id',
        'status',
        'notes',
        'max_participants',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'max_participants' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'started_at', 'ended_at'])
            ->logOnlyDirty();
    }

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    // Helper Methods
    public function startSession(): void
    {
        $this->update([
            'started_at' => now(),
            'status' => 'in-progress',
        ]);
    }

    public function endSession(): void
    {
        $started = $this->started_at;
        $ended = now();
        $duration = $started->diffInMinutes($ended);

        $this->update([
            'ended_at' => $ended,
            'duration_minutes' => $duration,
            'status' => 'completed',
        ]);
    }

    public function cancelSession(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function getAttendanceRate(): float
    {
        $total = $this->course->activeEnrollments()->count();
        if ($total === 0) return 0;

        $present = $this->attendances()->where('status', 'present')->count();
        return round(($present / $total) * 100, 2);
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in-progress';
    }

    public function canStart(): bool
    {
        return $this->status === 'scheduled' && $this->scheduled_at->isPast();
    }
}

// ============================================
// 8. ATTENDANCE MODEL
// ============================================
class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_session_id',
        'student_id',
        'status',
        'joined_at',
        'left_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function classSession(): BelongsTo
    {
        return $this->belongsTo(ClassSession::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    // Scopes
    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    // Helper Methods
    public function markPresent(): void
    {
        $this->update([
            'status' => 'present',
            'joined_at' => now(),
        ]);
    }

    public function markAbsent(): void
    {
        $this->update(['status' => 'absent']);
    }

    public function recordExit(): void
    {
        if (!$this->joined_at) return;

        $duration = $this->joined_at->diffInMinutes(now());
        
        $this->update([
            'left_at' => now(),
            'duration_minutes' => $duration,
        ]);
    }
}

// ============================================
// 9. ASSIGNMENT MODEL
// ============================================
class Assignment extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'due_at', 'status', 'max_score'])
            ->logOnlyDirty();
    }

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasManyThrough
    {
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

// ============================================
// 10. SUBMISSION MODEL
// ============================================
class Submission extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'submitted_at'])
            ->logOnlyDirty();
    }

    // Relationships
    public function assignment(): BelongsTo
    {
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

    // Scopes
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



// ============================================
// 11. GRADE MODEL
// ============================================
class Grade extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'submission_id',
        'instructor_id',
        'score',
        'max_score',
        'percentage',
        'letter_grade',
        'feedback',
        'graded_at',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'graded_at' => 'datetime',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['score', 'percentage', 'is_published'])
            ->logOnlyDirty();
    }

    // Relationships
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePassing($query, float $passingPercentage = 60)
    {
        return $query->where('percentage', '>=', $passingPercentage);
    }

    public function scopeFailing($query, float $passingPercentage = 60)
    {
        return $query->where('percentage', '<', $passingPercentage);
    }

    // Helper Methods
    public function calculatePercentage(): void
    {
        if ($this->max_score > 0) {
            $percentage = ($this->score / $this->max_score) * 100;
            $this->update(['percentage' => round($percentage, 2)]);
        }
    }

    public function calculateLetterGrade(): void
    {
        $percentage = $this->percentage;
        
        $letterGrade = match(true) {
            $percentage >= 90 => 'A',
            $percentage >= 80 => 'B',
            $percentage >= 70 => 'C',
            $percentage >= 60 => 'D',
            default => 'F',
        };

        $this->update(['letter_grade' => $letterGrade]);
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Fire event to notify student
        event(new \App\Events\GradePublished($this));
    }

    public function isPassing(float $threshold = 60): bool
    {
        return $this->percentage >= $threshold;
    }
}

// ============================================
// 12. MATERIAL MODEL
// ============================================
class Material extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'external_url',
        'download_count',
        'is_downloadable',
        'uploaded_at',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'is_downloadable' => 'boolean',
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'type'])
            ->logOnlyDirty();
    }

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return 'N/A';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function hasExternalUrl(): bool
    {
        return !empty($this->external_url);
    }
}

// ============================================
// 13. NOTIFICATION MODEL
// ============================================
class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'priority',
        'is_read',
        'read_at',
        'channel',
    ];

    protected $casts = [
        'data' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    // Helper Methods
    public function markAsRead(): void
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread(): void
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function isUrgent(): bool
    {
        return $this->priority === 'urgent';
    }
}

// ============================================
// 14. SERVICE REQUEST MODEL
// ============================================
class ServiceRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'service_type',
        'title',
        'description',
        'details',
        'status',
        'priority',
        'assigned_to',
        'estimated_cost',
        'final_cost',
        'requested_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'details' => 'array',
        'estimated_cost' => 'decimal:2',
        'final_cost' => 'decimal:2',
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'priority', 'assigned_to'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function taxCalculation(): HasOne
    {
        return $this->hasOne(TaxCalculation::class);
    }

    public function businessSetup(): HasOne
    {
        return $this->hasOne(BusinessSetup::class);
    }

    public function counselingSession(): HasOne
    {
        return $this->hasOne(CounselingSession::class);
    }

    public function foreignShopping(): HasOne
    {
        return $this->hasOne(ForeignShopping::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in-progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    // Helper Methods
    public function assignTo(int $userId): void
    {
        $this->update([
            'assigned_to' => $userId,
            'status' => 'in-progress',
        ]);
    }

    public function markCompleted(float $finalCost = null): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'final_cost' => $finalCost ?? $this->estimated_cost,
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }
}

// ============================================
// 15. TAX CALCULATION MODEL
// ============================================
class TaxCalculation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'service_request_id',
        'tax_type',
        'tax_year',
        'income_amount',
        'deductions',
        'taxable_amount',
        'tax_due',
        'calculation_details',
        'recommendations',
    ];

    protected $casts = [
        'tax_year' => 'integer',
        'income_amount' => 'decimal:2',
        'deductions' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'tax_due' => 'decimal:2',
        'calculation_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // Helper Methods
    public function calculateTax(): void
    {
        $taxableAmount = $this->income_amount - $this->deductions;
        
        // Nigerian tax calculation (simplified example)
        $taxDue = match(true) {
            $taxableAmount <= 300000 => $taxableAmount * 0.07,
            $taxableAmount <= 600000 => 21000 + (($taxableAmount - 300000) * 0.11),
            $taxableAmount <= 1100000 => 54000 + (($taxableAmount - 600000) * 0.15),
            $taxableAmount <= 1600000 => 129000 + (($taxableAmount - 1100000) * 0.19),
            $taxableAmount <= 3200000 => 224000 + (($taxableAmount - 1600000) * 0.21),
            default => 560000 + (($taxableAmount - 3200000) * 0.24),
        };

        $this->update([
            'taxable_amount' => $taxableAmount,
            'tax_due' => round($taxDue, 2),
        ]);
    }

    public function getEffectiveTaxRate(): float
    {
        if ($this->income_amount <= 0) return 0;
        
        return round(($this->tax_due / $this->income_amount) * 100, 2);
    }
}

// ============================================
// 16. BUSINESS SETUP MODEL
// ============================================
class BusinessSetup extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'user_id',
        'service_request_id',
        'business_name',
        'business_type',
        'business_description',
        'industry',
        'stage',
        'required_documents',
        'completed_steps',
        'registration_date',
        'registration_number',
        'setup_cost',
        'assigned_consultant_id',
    ];

    protected $casts = [
        'required_documents' => 'array',
        'completed_steps' => 'array',
        'registration_date' => 'date',
        'setup_cost' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['stage', 'business_name', 'registration_number'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function consultant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_consultant_id');
    }

    // Helper Methods
    public function moveToNextStage(): void
    {
        $stages = ['idea', 'planning', 'registration', 'setup', 'operational', 'monitoring'];
        $currentIndex = array_search($this->stage, $stages);
        
        if ($currentIndex !== false && $currentIndex < count($stages) - 1) {
            $this->update(['stage' => $stages[$currentIndex + 1]]);
        }
    }

    public function completeStep(string $step): void
    {
        $steps = $this->completed_steps ?? [];
        if (!in_array($step, $steps)) {
            $steps[] = $step;
            $this->update(['completed_steps' => $steps]);
        }
    }

    public function getProgressPercentage(): float
    {
        $requiredDocs = $this->required_documents ?? [];
        $completedSteps = $this->completed_steps ?? [];
        
        if (empty($requiredDocs)) return 0;
        
        $completed = count(array_intersect($requiredDocs, $completedSteps));
        return round(($completed / count($requiredDocs)) * 100, 2);
    }

    public function isRegistered(): bool
    {
        return !empty($this->registration_number);
    }
}

// ============================================
// 17. COUNSELING SESSION MODEL
// ============================================
class CounselingSession extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'service_request_id',
        'session_type',
        'scheduled_at',
        'started_at',
        'ended_at',
        'duration_minutes',
        'google_meet_link',
        'reason',
        'notes',
        'action_items',
        'status',
        'requires_follow_up',
        'follow_up_date',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'requires_follow_up' => 'boolean',
        'follow_up_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'scheduled_at', 'requires_follow_up'])
            ->logOnlyDirty();
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled');
    }

    // Helper Methods
    public function startSession(): void
    {
        $this->update([
            'started_at' => now(),
            'status' => 'in-progress',
        ]);
    }

    public function endSession(): void
    {
        $duration = $this->started_at->diffInMinutes(now());
        
        $this->update([
            'ended_at' => now(),
            'duration_minutes' => $duration,
            'status' => 'completed',
        ]);
    }

    public function scheduleFollowUp(string $date): void
    {
        $this->update([
            'requires_follow_up' => true,
            'follow_up_date' => $date,
        ]);
    }
}

// ============================================
// 18. FOREIGN SHOPPING MODEL
// ============================================
class ForeignShopping extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'foreign_shopping';

    protected $fillable = [
        'user_id',
        'service_request_id',
        'item_name',
        'item_description',
        'item_url',
        'item_price_usd',
        'quantity',
        'total_price_usd',
        'estimated_shipping',
        'service_fee',
        'total_cost_naira',
        'delivery_address',
        'delivery_city',
        'delivery_state',
        'delivery_phone',
        'status',
        'expected_delivery_date',
        'actual_delivery_date',
        'tracking_number',
        'notes',
    ];

    protected $casts = [
        'item_price_usd' => 'decimal:2',
        'quantity' => 'integer',
        'total_price_usd' => 'decimal:2',
        'estimated_shipping' => 'decimal:2',
        'service_fee' => 'decimal:2',
        'total_cost_naira' => 'decimal:2',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'tracking_number', 'actual_delivery_date'])
            ->logOnlyDirty();
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    // Accessors
    public function getFullDeliveryAddressAttribute(): string
    {
        return trim("{$this->delivery_address}, {$this->delivery_city}, {$this->delivery_state}");
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['requested', 'quote-sent', 'payment-pending']);
    }

    public function scopeInTransit($query)
    {
        return $query->whereIn('status', ['paid', 'purchasing', 'shipping']);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    // Helper Methods
    public function calculateTotalCost(float $exchangeRate): void
    {
        $totalUsd = $this->total_price_usd + ($this->estimated_shipping ?? 0) + ($this->service_fee ?? 0);
        $totalNaira = $totalUsd * $exchangeRate;
        
        $this->update(['total_cost_naira' => round($totalNaira, 2)]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => 'delivered',
            'actual_delivery_date' => now(),
        ]);
    }

    public function updateTracking(string $trackingNumber): void
    {
        $this->update(['tracking_number' => $trackingNumber]);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }
}

// ============================================
// 19. REPORT MODEL
// ============================================
class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'generated_by',
        'student_id',
        'instructor_id',
        'parent_id',
        'report_type',
        'title',
        'description',
        'data',
        'file_path',
        'period_start',
        'period_end',
        'generated_at',
    ];

    protected $casts = [
        'data' => 'array',
        'period_start' => 'date',
        'period_end' => 'date',
        'generated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    // Scopes
    public function scopeByType($query, string $type)
    {
        return $query->where('report_type', $type);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForInstructor($query, int $instructorId)
    {
        return $query->where('instructor_id', $instructorId);
    }

    public function scopeInPeriod($query, $start, $end)
    {
        return $query->whereBetween('period_start', [$start, $end]);
    }

    // Helper Methods
    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }
}

// ============================================
// 20. STUDENT PARENT PIVOT MODEL
// ============================================
class StudentParent extends Model
{
    protected $table = 'student_parent';

    protected $fillable = [
        'student_id',
        'parent_id',
        'relationship',
        'is_primary_contact',
        'can_view_grades',
        'can_view_attendance',
    ];

    protected $casts = [
        'is_primary_contact' => 'boolean',
        'can_view_grades' => 'boolean',
        'can_view_attendance' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }
}

// ============================================
// 21. INSTRUCTOR COURSE PIVOT MODEL
// ============================================
class InstructorCourse extends Model
{
    protected $table = 'instructor_course';

    protected $fillable = [
        'instructor_id',
        'course_id',
        'assigned_date',
        'is_primary_instructor',
    ];

    protected $casts = [
        'assigned_date' => 'date',
        'is_primary_instructor' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}

/*
┌─────────────────────────────────────────────────────────────────┐
│                    MODELS SUMMARY                                │
└─────────────────────────────────────────────────────────────────┘

✅ TOTAL MODELS: 21

CORE MODELS (11):
├── User (Authentication base)
├── Student
├── ParentModel
├── Instructor
├── Course
├── Enrollment
├── ClassSession
├── Attendance
├── Assignment
├── Submission
└── Grade

SUPPORT MODELS (4):
├── Material
├── Notification
├── Report
└── StudentParent (Pivot)

SERVICE MODELS (5):
├── ServiceRequest
├── TaxCalculation
├── BusinessSetup
├── CounselingSession
└── ForeignShopping

PIVOT MODELS (2):
├── StudentParent
└── InstructorCourse

┌─────────────────────────────────────────────────────────────────┐
│                    FEATURES INCLUDED                             │
└─────────────────────────────────────────────────────────────────┘

✅ Proper Relationships (BelongsTo, HasMany, BelongsToMany)
✅ Soft Deletes on all major models
✅ Activity Logging (Spatie)
✅ Role Management (Spatie Permission)
✅ Type Casting for dates, decimals, arrays
✅ Accessors & Mutators for computed properties
✅ Query Scopes for reusable filters
✅ Helper Methods for business logic
✅ Pivot table models for many-to-many relationships
✅ Proper foreign key relationships

┌─────────────────────────────────────────────────────────────────┐
│                    USAGE EXAMPLES                                │
└─────────────────────────────────────────────────────────────────┘

// Get student with all relationships
$student = Student::with(['user', 'enrollments.course', 'parents'])->find(1);

// Get instructor's monthly hours
$instructor = Instructor::find(1);
$hours = $instructor->calculateMonthlyHours();

// Mark attendance
$attendance = Attendance::find(1);
$attendance->markPresent();

// Calculate grade
$grade = Grade::find(1);
$grade->calculatePercentage();
$grade->calculateLetterGrade();
$grade->publish();

// Get parent's children progress
$parent = ParentModel::find(1);
$progress = $parent->getChildrenProgress();

// Start class session
$session = ClassSession::find(1);
$session->startSession();
// ... later
$session->endSession();

// Check if course is full
$course = Course::find(1);
if ($course->isFull()) {
    // Handle full course
}

┌─────────────────────────────────────────────────────────────────┐
│                    NEXT STEPS                                    │
└─────────────────────────────────────────────────────────────────┘

MODELS COMPLETE ✓

Ready to build:
→ Repository Interfaces
→ Repository Implementations
→ Service Interfaces
→ Service Implementations
→ Service Provider Bindings

*/

