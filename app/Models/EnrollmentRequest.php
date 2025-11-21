<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Events\EnrollmentApproved;
use App\Events\EnrollmentRejected;
use Illuminate\Database\Eloquent\Model;
use App\Events\EnrollmentRequestCreated;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnrollmentRequest extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'student_id',
        'course_id',
        'request_code',
        'frequency_preference',
        'quoted_price',
        'currency',
        'student_message',
        'status',
        'rejection_reason',
        'processed_by',
        'processed_at',
        'enrollment_id',
    ];

    protected $casts = [
        'quoted_price' => 'decimal:2',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Boot method to generate request code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            if (empty($request->request_code)) {
                $request->request_code = self::generateRequestCode();
            }

            // Set quoted price based on frequency
            if (empty($request->quoted_price) && $request->course) {
                $request->quoted_price = match($request->frequency_preference) {
                    '3x_weekly' => $request->course->price_3x_weekly ?? 0,
                    '5x_weekly' => $request->course->price_5x_weekly ?? 0,
                    default => 0,
                };
            }
        });
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeParentNotified($query)
    {
        return $query->where('status', 'parent_notified');
    }

    public function scopePaymentPending($query)
    {
        return $query->where('status', 'payment_pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pending', 'parent_notified', 'payment_pending']);
    }

    // Helper Methods
    public static function generateRequestCode(): string
    {
        do {
            $code = 'ENR-' . strtoupper(Str::random(8));
        } while (self::where('request_code', $code)->exists());

        return $code;
    }

    public function notifyParent(): void
    {
        $this->update(['status' => 'parent_notified']);
        
        event(new EnrollmentRequestCreated($this));
    }

    public function markPaymentPending(): void
    {
        $this->update(['status' => 'payment_pending']);
    }

    public function approve(int $adminUserId): bool
    {
        // Check if already enrolled
        if ($this->student->courses()->where('course_id', $this->course_id)->exists()) {
            $this->update([
                'status' => 'rejected',
                'rejection_reason' => 'Student is already enrolled in this course',
                'processed_by' => $adminUserId,
                'processed_at' => now(),
            ]);
            return false;
        }

        // Create enrollment
        $enrollment = Enrollment::create([
            'student_id' => $this->student_id,
            'course_id' => $this->course_id,
            'enrolled_at' => now(),
            'status' => 'active',
            'progress_percentage' => 0,
        ]);

        // Update request
        $this->update([
            'status' => 'approved',
            'enrollment_id' => $enrollment->id,
            'processed_by' => $adminUserId,
            'processed_at' => now(),
        ]);

        // TODO: Send notification to student and parents
       event(new EnrollmentApproved($this));

        return true;
    }

    public function reject(int $adminUserId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'processed_by' => $adminUserId,
            'processed_at' => now(),
        ]);

        // TODO: Send notification to student
        event(new EnrollmentRejected($this));
    }

    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'processed_at' => now(),
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isParentNotified(): bool
    {
        return $this->status === 'parent_notified';
    }

    public function isPaymentPending(): bool
    {
        return $this->status === 'payment_pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'parent_notified', 'payment_pending']);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'parent_notified' => 'info',
            'payment_pending' => 'primary',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending Review',
            'parent_notified' => 'Parent Notified',
            'payment_pending' => 'Awaiting Payment',
            'approved' => 'Approved & Enrolled',
            'rejected' => 'Rejected',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->quoted_price, 2);
    }

    public function getFrequencyTextAttribute(): string
    {
        return match($this->frequency_preference) {
            '3x_weekly' => '3 times per week',
            '5x_weekly' => '5 times per week',
            default => $this->frequency_preference,
        };
    }
}