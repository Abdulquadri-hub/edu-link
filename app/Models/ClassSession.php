<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassSession extends Model
{
    protected $fillable = [
        'course_id','instructor_id','title','description','scheduled_at','started_at','ended_at','duration_minutes','google_meet_link','google_calendar_event_id','status','notes','max_participants',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_minutes' => 'integer',
        'max_participants' => 'integer',
    ];

    public function course(): BelongsTo {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo {
        return $this->belongsTo(Instructor::class);
    }

    public function attendances(): HasMany {
        return $this->hasMany(Attendance::class);
    }

    public function scopeScheduled($query) {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query) {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query) {
        return $query->where('scheduled_at', '>', now())
                    ->where('status', 'scheduled');
    }

    public function scopeToday($query) {
        return $query->whereDate('scheduled_at', today());
    }

    public function startSession(): void {
        $this->update([
            'started_at' => now(),
            'status' => 'in_progress'
        ]);
    }

    public function endSession(): void {
        $started = $this->started_at;
        $ended = now();
        $duration = $started->diffInMinutes($ended);

        $this->update([
            'ended_at' => $ended,
            'duration_minutes' => $duration,
            'status' => 'completed',
        ]);
    }

    public function cancelSession(): void {
        $this->update(['status' => 'cancelled']);
    }

    public function isInprogress(): bool {
        return $this->status === "in-progress";
    }

    public function canStart(): bool {
        return $this->status === "scheduled" && $this->scheduled_at->isPast();
    }
    
}
