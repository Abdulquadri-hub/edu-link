<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    // use SoftDeletes;

    protected $fillable = [
        'student_id',
        'course_id',
        'payment_id',
        'subscription_code',
        'frequency',
        'start_date',
        'end_date',
        'status',
        'total_sessions',
        'sessions_attended',
        'sessions_remaining',
        'notes',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_sessions' => 'integer',
        'sessions_attended' => 'integer',
        'sessions_remaining' => 'integer',
        'cancelled_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Boot method to generate subscription code
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($subscription) {
            if (empty($subscription->subscription_code)) {
                $subscription->subscription_code = self::generateSubscriptionCode();
            }

            // Calculate total sessions based on frequency and duration
            if (empty($subscription->total_sessions)) {
                $subscription->calculateTotalSessions();
            }

            // Initialize sessions remaining
            if (empty($subscription->sessions_remaining)) {
                $subscription->sessions_remaining = $subscription->total_sessions;
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

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'active')
                  ->where('end_date', '<', now());
            });
    }

    public function scopeExpiringSoon($query, int $days = 7)
    {
        return $query->where('status', 'active')
            ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    // Helper Methods
    public static function generateSubscriptionCode(): string
    {
        do {
            $code = 'SUB-' . strtoupper(Str::random(10));
        } while (self::where('subscription_code', $code)->exists());

        return $code;
    }

    public function calculateTotalSessions(): void
    {
        $weeks = $this->start_date->diffInWeeks($this->end_date);
        
        $sessionsPerWeek = match($this->frequency) {
            '3x_weekly' => 3,
            '5x_weekly' => 5,
            default => 3,
        };

        $this->total_sessions = $weeks * $sessionsPerWeek;
    }

    public function recordAttendance(): void
    {
        $this->increment('sessions_attended');
        $this->decrement('sessions_remaining');
        
        // Check if all sessions are used
        if ($this->sessions_remaining <= 0) {
            $this->markAsExpired('All sessions completed');
        }
    }

    public function checkExpiry(): void
    {
        if ($this->end_date->isPast() && $this->status === 'active') {
            $this->markAsExpired('Subscription period ended');
        }
    }

    public function markAsExpired(?string $reason = null): void
    {
        $this->update([
            'status' => 'expired',
            'notes' => $reason ?? $this->notes,
        ]);
    }

    public function cancel(string $reason): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
        ]);
    }

    public function suspend(): void
    {
        $this->update(['status' => 'suspended']);
    }

    public function reactivate(): void
    {
        if ($this->end_date->isFuture()) {
            $this->update(['status' => 'active']);
        }
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->end_date->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || $this->end_date->isPast();
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isExpiringSoon(int $days = 7): bool
    {
        return $this->status === 'active' && 
               $this->end_date->isBetween(now(), now()->addDays($days));
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, $this->end_date->diffInDays(now()));
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->total_sessions === 0) return 0;
        
        return round(($this->sessions_attended / $this->total_sessions) * 100, 2);
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'success',
            'expired' => 'danger',
            'cancelled' => 'warning',
            'suspended' => 'gray',
            default => 'gray',
        };
    }

    public function getFrequencyTextAttribute(): string
    {
        return match($this->frequency) {
            '3x_weekly' => '3 times per week',
            '5x_weekly' => '5 times per week',
            default => $this->frequency,
        };
    }
}