<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    // use SoftDeletes;
    
    protected $fillable = [
        "course_code", "title", "description", "category", 
        "level", "duration_weeks", "credit_hours", "price", "thumbnail", "learning_objectives", "prerequisites", "status", "max_students", "academic_level_id"
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_weeks' => 'integer',
        'learning_objectives' => 'array',
        'credit_hours' => 'integer',
        'max_students' => 'integer',
    ];

    // relationships

    public function academicLevel(): BelongsTo {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function instructors(): BelongsToMany {
        return $this->belongsToMany(Instructor::class, "instructor_course", "course_id", "instructor_id")->withPivot(['assigned_date', 'is_primary_instructor'])->withTimestamps();
    }

    public function primaryInstructor(): BelongsToMany {
        return $this->instructors()->wherePivot("is_primary_instructor", true);
    }

    public function enrollments(): HasMany {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany {
        return $this->enrollments()->where('status', 'active');
    }

    public function students(): BelongsToMany {
        return $this->belongsToMany(Student::class, "enrollments")->withPivot(['enrolled_at', 'status', 'progress_percentage', 'final_grade'])->withTimestamps();
    }

    public function classSessions(): HasMany {
        return $this->hasMany(ClassSession::class);
    }

    public function upcomingSessions(): HasMany {
        return $this->classSessions()
            ->where('scheduled_at', '>', now())
            ->where('status', 'scheduled');
    }

    public function assignments(): HasMany {
        return $this->hasMany(Assignment::class);
    }

    public function materials(): HasMany {
        return $this->hasMany(Material::class);
    }

    public function scopeByAcademicLevel($query, int $levelId)
    {
        return $query->where('academic_level_id', $levelId);
    }

    public function scopeElementary($query)
    {
        return $query->whereHas('academicLevel', function ($q) {
            $q->where('level_type', 'elementary');
        });
    }

    public function scopeMiddle($query)
    {
        return $query->whereHas('academicLevel', function ($q) {
            $q->where('level_type', 'middle');
        });
    }

    public function scopeHigh($query)
    {
        return $query->whereHas('academicLevel', function ($q) {
            $q->where('level_type', 'high');
        });
    }

    public function getGradeLevelName(): ?string
    {
        return $this->academicLevel?->name;
    }

    public function getGradeNumber(): ?int
    {
        return $this->academicLevel?->grade_number;
    }

    public function isForGradeLevel(int $gradeNumber): bool
    {
        return $this->academicLevel?->grade_number === $gradeNumber;
    }
}
