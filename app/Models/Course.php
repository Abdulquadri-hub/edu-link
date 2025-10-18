<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use SoftDeletes;
    
    protected $fillable = [
        "course_code", "title", "description", "category", 
        "level", "duration_weeks", "credit_hours", "price", "thumbnail", "learning_objectives", "prerequisites", "status", "max_students", ""
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'duration_weeks' => 'integer',
        'learning_objectives' => 'array',
        'credit_hours' => 'integer',
        'max_students' => 'integer',
    ];

    // relationships

    public function instructors(): BelongsToMany {
        return $this->belongsToMany(Instructor::class, "instructor_course")->withPivot(['assigned_date', 'is_primary_instructor'])->withTimestamps();
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

    //accessors 

    // scopes

    // helpers
}
