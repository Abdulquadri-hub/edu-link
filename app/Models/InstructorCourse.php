<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
