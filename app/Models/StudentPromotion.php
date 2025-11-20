<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    protected $fillable = [
        'student_id',
        'from_academic_level_id',
        'to_academic_level_id',
        'promoted_by_id',
        'promoted_by_type',
        'reason',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromAcademicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'from_academic_level_id');
    }

    public function toAcademicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'to_academic_level_id');
    }
}
