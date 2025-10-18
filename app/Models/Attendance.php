<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'class_session_id',
        'student_id',
        'status',
        'joined_at',
        'left_at',
        'duration_minutes',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'left_at' => 'datetime',
        'duration_minutes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function classSession(): BelongsTo {
        return $this->belongsTo(classSession::class);
    }

    public function student(): BelongsTo {
        return $this->belongsTo(student::class);
    }

    public function scopePesent($query) {
        $query->where('status', 'present');
    }

    public function scopeAbsent($query){
        return $query->where('status', 'absent');
    }

    //helpers

    public function markPresent(): void {
        $this->update([
            'status' => 'present',
            'joined_at' => now()
        ]);
    }

    public function markAbsent(): void {
        $this->update([
            'status' => 'absent',
        ]);
    }

    public function recordExit(): void {
        if(!$this->joined_at) return;

        $duration = $this->joined_at->diffInMinutes(now());

        $this->update([
            'left_at' => now(),
            'duration_minutes' => $duration
        ]);
    }

}
