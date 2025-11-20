<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AcademicLevel extends Model
{

    protected $fillable = [
        'name',
        'grade_number',
        'description',
        'level_type',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'grade_number' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeElementary($query)
    {
        return $query->where('level_type', 'elementary');
    }

    public function scopeMiddle($query)
    {
        return $query->where('level_type', 'middle');
    }

    public function scopeHigh($query)
    {
        return $query->where('level_type', 'high');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('grade_number');
    }

    // Accessors
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} (Grade {$this->grade_number})";
    }

    // Helper Methods
    public function isElementary(): bool
    {
        return $this->level_type === 'elementary';
    }

    public function isMiddle(): bool
    {
        return $this->level_type === 'middle';
    }

    public function isHigh(): bool
    {
        return $this->level_type === 'high';
    }

    public function getNextLevel(): ?self
    {
        return self::where('grade_number', $this->grade_number + 1)
                   ->where('is_active', true)
                   ->first();
    }

    public function getPreviousLevel(): ?self
    {
        return self::where('grade_number', $this->grade_number - 1)
                   ->where('is_active', true)
                   ->first();
    }

    public function getActiveStudentsCount(): int
    {
        return $this->students()
                    ->where('enrollment_status', 'active')
                    ->count();
    }

    public function getActiveCoursesCount(): int
    {
        return $this->courses()
                    ->where('status', 'active')
                    ->count();
    }
}