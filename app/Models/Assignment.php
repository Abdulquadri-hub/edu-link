<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Assignment extends Model
{
    use SoftDeletes;

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

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(Instructor::class);
    }

    public function submissions(): HasMany {
        return $this->hasMany(Submission::class);
    }

    public function grades(): HasManyThrough {
        return $this->hasManyThrough(Grade::class, Submission::class);
    }

}
