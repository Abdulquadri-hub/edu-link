<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentRegistration extends Model
{
    protected $fillable = [
        'student_id',
        'enrollment_request_id',
        'registration_code',
        'parent_first_name',
        'parent_last_name',
        'parent_email',
        'parent_phone',
        'relationship',
        'temporary_password',
        'status',
        'created_parent_id',
        'created_user_id',
        'email_sent_at',
        'completed_at',
        'expires_at',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $hidden = [
        'temporary_password',
    ];

    // Boot method
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            // Generate registration code
            if (empty($registration->registration_code)) {
                $registration->registration_code = self::generateRegistrationCode();
            }

            // Set expiry (7 days from now)
            if (empty($registration->expires_at)) {
                $registration->expires_at = now()->addDays(7);
            }
        });
    }

    // Relationships
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function enrollmentRequest(): BelongsTo
    {
        return $this->belongsTo(EnrollmentRequest::class);
    }

    public function createdParent(): BelongsTo
    {
        return $this->belongsTo(ParentModel::class, 'created_parent_id');
    }

    public function createdUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired')
            ->orWhere(function ($q) {
                $q->where('status', 'pending')
                  ->where('expires_at', '<', now());
            });
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    // Helper Methods
    public static function generateRegistrationCode(): string
    {
        do {
            $code = 'PREG-' . strtoupper(Str::random(10));
        } while (self::where('registration_code', $code)->exists());

        return $code;
    }

    public static function generateTemporaryPassword(): string
    {
        // Generate a secure random password (8 characters)
        return Str::random(8);
    }

    public function createParentAccount(): bool
    {
        try {
            // Check if email already exists
            $existingUser = User::where('email', $this->parent_email)->first();
            
            if ($existingUser) {
                // Check if it's a parent account
                if ($existingUser->user_type === 'parent' && $existingUser->parent) {
                    // Link existing parent to student
                    $this->linkExistingParent($existingUser->parent);
                    return true;
                }
                
                return false; // Email exists but not a parent
            }

            // Create new user account
            $user = User::create([
                'email' => $this->parent_email,
                'username' => $this->generateUsername(),
                'password' => Hash::make($this->temporary_password),
                'first_name' => $this->parent_first_name,
                'last_name' => $this->parent_last_name,
                'phone' => $this->parent_phone,
                'user_type' => 'parent',
                'status' => 'active',
                'email_verified_at' => null, // Will verify on first login
            ]);

            // Create parent record
            $parent = ParentModel::create([
                'user_id' => $user->id,
                'parent_id' => 'PAR-' . str_pad($user->id, 6, '0', STR_PAD_LEFT),
            ]);

            // Link parent to student
            $parent->children()->attach($this->student_id, [
                'relationship' => $this->relationship,
                'is_primary_contact' => true, // First parent is primary by default
                'can_view_grades' => true,
                'can_view_attendance' => true,
            ]);

            // Update registration record
            $this->update([
                'created_parent_id' => $parent->id,
                'created_user_id' => $user->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Parent account creation failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function linkExistingParent(ParentModel $parent): void
    {
        // Check if already linked
        if (!$parent->children()->where('student_id', $this->student_id)->exists()) {
            $parent->children()->attach($this->student_id, [
                'relationship' => $this->relationship,
                'is_primary_contact' => !$this->student->hasLinkedParent(), // Primary if first
                'can_view_grades' => true,
                'can_view_attendance' => true,
            ]);
        }

        // Update registration
        $this->update([
            'created_parent_id' => $parent->id,
            'created_user_id' => $parent->user_id,
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    protected function generateUsername(): string
    {
        $base = strtolower($this->parent_first_name . $this->parent_last_name);
        $base = preg_replace('/[^a-z0-9]/', '', $base);
        
        $username = $base;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $base . $counter;
            $counter++;
        }
        
        return $username;
    }

    public function sendWelcomeEmail(): void
    {
        // Send email notification
        \Illuminate\Support\Facades\Notification::route('mail', $this->parent_email)
            ->notify(new \App\Notifications\ParentAccountCreatedNotification($this, $this->temporary_password));
        
        $this->update(['email_sent_at' => now()]);
    }

    public function markCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function markExpired(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function isExpired(): bool
    {
        return $this->status === 'expired' || 
               ($this->status === 'pending' && $this->expires_at->isPast());
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getParentFullNameAttribute(): string
    {
        return "{$this->parent_first_name} {$this->parent_last_name}";
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'completed' => 'success',
            'expired' => 'danger',
            default => 'gray',
        };
    }

    public function getDaysUntilExpiryAttribute(): int
    {
        if ($this->isExpired()) return 0;
        
        return max(0, $this->expires_at->diffInDays(now()));
    }
}