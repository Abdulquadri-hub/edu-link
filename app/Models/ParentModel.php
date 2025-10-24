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

    //casts
    
    protected $casts = [
        "recieves_weekly_report" => 'boolean'
    ];
    
    //relationships
    
    public function user():BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function children(): BelongsToMany {
        return $this->belongsToMany(Student::class, "student_parent", "parent_id", "student_id")->withPivot(["relationship", "is_primary_contact", "can_view_grades", "can_view_attendance"])->withTimestamps();
    }

    public function primaryChildren(): BelongsToMany {
        return $this->children()->wherePivot('is_primary_contact', true);
    }

    public function reports(): HasMany {
        return $this->hasMany(Report::class, "parent_id");
    }

    // accessors / mutators

    public function getFullAddressAttribute(): string {
        return trim("{$this->address}, {$this->city}, {$this->state}, {$this->country}");
    }
    
    //helpers

    public function canViewChildGrades(int $childId): bool {
        return $this->children()
            ->where('student_id', $childId)
            ->wherePivot('can_view_grades', true)
            ->exists();
    }

    public function canViewChildAttendance(int $childId): bool {
        return $this->children()
            ->where('student_id', $childId)
            ->wherePivot('can_view_attendance', true)
            ->exists();
    }

    public function getChildrenProgress(): array {
        return $this->children()->map(function ($child) {
            return [
                "student" => $child->student_id,
                "name" => $child->user->full_name,
                "progress" => $child->calculateOveralProgress(),
                "attendance_rate" => $child->calculateAttendanceRate()
            ];
        })->toArray();
    }
}
