<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPromotion extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'student_id',
        'from_level_id',
        'to_level_id',
        'promotion_code',
        'promotion_type',
        'academic_year',
        'final_gpa',
        'promotion_notes',
        'status',
        'rejection_reason',
        'promoted_by',
        'approved_by',
        'promotion_date',
        'approved_at',
        'effective_date',
        'auto_update_enrollments',
    ];

    protected $casts = [
        'final_gpa' => 'decimal:2',
        'promotion_date' => 'datetime',
        'approved_at' => 'datetime',
        'effective_date' => 'datetime',
        'auto_update_enrollments' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Boot method to generate promotion code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($promotion) {
            if (empty($promotion->promotion_code)) {
                $promotion->promotion_code = self::generatePromotionCode();
            }

            // Set effective date if not provided
            if (empty($promotion->effective_date)) {
                $promotion->effective_date = now();
            }
        });
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function fromLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'from_level_id');
    }

    public function toLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class, 'to_level_id');
    }

    public function promoter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'promoted_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function scopeRegular($query)
    {
        return $query->where('promotion_type', 'regular');
    }

    // Helper Methods
    public static function generatePromotionCode(): string
    {
        do {
            $code = 'PROM-' . strtoupper(Str::random(8));
        } while (self::where('promotion_code', $code)->exists());

        return $code;
    }

    public function approve(int $adminUserId): bool
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $adminUserId,
            'approved_at' => now(),
        ]);

        // Execute the promotion
        return $this->execute();
    }

    public function reject(int $adminUserId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'approved_by' => $adminUserId,
            'approved_at' => now(),
        ]);

        // TODO: Send notification to promoter
        // $this->promoter->notify(new PromotionRejected($this));
    }

    public function execute(): bool
    {
        // Update student's academic level
        $this->student->update([
            'academic_level_id' => $this->to_level_id,
        ]);

        // Update status to completed
        $this->update(['status' => 'completed']);

        // Update enrollments if requested
        if ($this->auto_update_enrollments) {
            $this->updateEnrollments();
        }

        // TODO: Send notification to student and parents
        // $this->student->user->notify(new StudentPromoted($this));
        // foreach ($this->student->parents as $parent) {
        //     $parent->user->notify(new StudentPromoted($this));
        // }

        return true;
    }

    protected function updateEnrollments(): void
    {
        // Get active enrollments
        $activeEnrollments = $this->student->enrollments()
            ->where('status', 'active')
            ->get();

        foreach ($activeEnrollments as $enrollment) {
            $course = $enrollment->course;
            
            // Check if course requires specific grade level
            if ($course->academic_level_id) {
                // If course level doesn't match new level, complete the enrollment
                if ($course->academic_level_id !== $this->to_level_id) {
                    $enrollment->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                        'notes' => 'Completed due to grade level promotion',
                    ]);
                }
            }
        }
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeApproved(): bool
    {
        return $this->status === 'pending';
    }

    public function isPromotion(): bool
    {
        if (!$this->from_level_id) return true;
        
        return $this->toLevel->grade_number > $this->fromLevel->grade_number;
    }

    public function isDemotion(): bool
    {
        if (!$this->from_level_id) return false;
        
        return $this->toLevel->grade_number < $this->fromLevel->grade_number;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'info',
            'completed' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Awaiting Approval',
            'approved' => 'Approved',
            'completed' => 'Completed',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function getPromotionTypeTextAttribute(): string
    {
        return match($this->promotion_type) {
            'regular' => 'Regular Promotion',
            'skip' => 'Skip Grade',
            'repeat' => 'Repeat Grade',
            'transfer' => 'Transfer',
            'manual' => 'Manual Promotion',
            default => ucfirst($this->promotion_type),
        };
    }

    public function getLevelChangeAttribute(): string
    {
        $from = $this->fromLevel?->name ?? 'None';
        $to = $this->toLevel->name;
        
        return "{$from} → {$to}";
    }
}