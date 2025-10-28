<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $fillable = [
        'course_id',
        'instructor_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_size',
        'external_url',
        'download_count',
        'is_downloadable',
        'uploaded_at',
        'status',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'download_count' => 'integer',
        'is_downloadable' => 'boolean',
        'uploaded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    // Accessors
    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) return 'N/A';

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helper Methods
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
    }

    public function isVideo(): bool
    {
        return $this->type === 'video';
    }

    public function isPdf(): bool
    {
        return $this->type === 'pdf';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path);
    }

    public function hasExternalUrl(): bool
    {
        return !empty($this->external_url);
    }
}
