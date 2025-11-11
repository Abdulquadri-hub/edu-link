<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instructor extends Model
{
    use SoftDeletes;

    // fillbales
    
    protected $fillable = [
        "user_id", "instructor_id", "qualification", "specialization", "years_of_experience", "bio", 
        "linkedin_url", "hourly_rate", "employment_type", "hire_date", "status"
    ];

    // casts

    protected $casts = [
        "hire_date" => 'datetime',
        "hourly_rate" => 'decimal:2'
    ];

    // relationships

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function courses(): BelongsToMany {
        return $this->belongsToMany(Course::class, "instructor_course", "instructor_id", "course_id")->withPivot(['assigned_date', 'is_primary_instructor'])->withTimestamps();
    }

    public function primaryCourses(): BelongsToMany {
        return $this->courses()->wherePivot('is_primary_instructor', true);
    }

    public function assignments():HasMany {
        return $this->hasMany(Assignment::class);
    }

    public function classSessions(): HasMany {
        return $this->hasMany(classSession::class);
    }

    public function completedSession() : HasMany {
        return $this->classSessions()->where('status', 'completed');
    }

    // access / mutators

    //scopes 
    public function scopeActive($query) {
        return $query->where('status', 'active');
    }

    public function scopeInActive($query) {
        return $query->where('status', 'inactive');
    }

    public function scopeFullTime($query) {
        return $query->where('employment_type', 'full-time');
    }

    // helpers
    public function calculateMonthlyHours(?int $month = null , ?int $year = null) {
        $month = $month ?? now()->month();
        $year = $year ?? now()->year();

        $totalMinutes = $this->completedSession()
            ->whereMonth('started_at', $month)
            ->whereYear('started_at', $year)
            ->sum('duration_minutes');

        return round($totalMinutes / 60, 2);
    }

    public function calculateMonthlyEarnings(?int $month = null , ?int $year = null) {
        $hours = $this->calculateMonthlyHours($month, $year);
        return round($hours * $this->hourly_rate, 2);
    }

    public function getStudentCount(): int
    {
        return Student::whereHas('enrollments.course.instructors', function ($query) {
            $query->where('instructor_course.instructor_id', $this->id);
        })->distinct()->count();
    }

    public function isTeaching(int $courseId): bool
    {
        return $this->courses()->where('course_id', $courseId)->exists();
    }
}
