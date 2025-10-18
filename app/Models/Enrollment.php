<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Enrollment extends Model
{
    protected $fillable = [
        "student_id", "course_id", "enrolled_at", "completed", "status", "progress_percentage", "final_grade", "notes"
    ];

    protected $casts = [
        "enrolled_at" => "datetime",
        "progress_percentage" => "decimal:2",
        "final_grade" => "decimal:2",
        "completed_at" => "datetime",
    ];

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }
}
