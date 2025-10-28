<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Grade extends Model
{
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

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

 
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
        // event(new \App\Events\GradePublished($this));
    }

    public function isPassing(float $threshold = 60): bool
    {
        return $this->percentage >= $threshold;
    }

}
