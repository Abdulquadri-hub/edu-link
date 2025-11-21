<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'student_id',
        'parent_id',
        'course_id',
        'payment_reference',
        'amount',
        'currency',
        'payment_method',
        'receipt_path',
        'receipt_filename',
        'parent_notes',
        'status',
        'admin_notes',
        'verified_by',
        'verified_at',
        'payment_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Boot method to generate reference
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->payment_reference)) {
                $payment->payment_reference = self::generatePaymentReference();
            }
        });
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'parent_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForStudent($query, int $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeForParent($query, int $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    // Helper Methods
    public static function generatePaymentReference(): string
    {
        do {
            $reference = 'PAY-' . strtoupper(Str::random(8));
        } while (self::where('payment_reference', $reference)->exists());

        return $reference;
    }

    public function verify(int $adminUserId, ?string $notes = null): bool
    {
        $this->update([
            'status' => 'verified',
            'admin_notes' => $notes,
            'verified_by' => $adminUserId,
            'verified_at' => now(),
        ]);

        // TODO: Send notification to parent
        // event(new PaymentVerified($this));

        return true;
    }

    public function reject(int $adminUserId, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'admin_notes' => $reason,
            'verified_by' => $adminUserId,
            'verified_at' => now(),
        ]);

        // TODO: Send notification to parent
        // event(new PaymentRejected($this));
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isVerified(): bool
    {
        return $this->status === 'verified';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function canBeVerified(): bool
    {
        return $this->status === 'pending';
    }

    public function hasSubscription(): bool
    {
        return $this->subscription()->exists();
    }

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_path ? asset('storage/' . $this->receipt_path) : null;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Awaiting Verification',
            'verified' => 'Verified',
            'rejected' => 'Rejected',
            default => 'Unknown',
        };
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }
}