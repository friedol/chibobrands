<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskUpdate extends Model
{
    protected $fillable = [
        'task_id',
        'admin_id',
        'type',
        'content',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Update types
    const TYPE_STATUS_UPDATE = 'status_update';
    const TYPE_COMMENT = 'comment';
    const TYPE_FILE_UPLOAD = 'file_upload';
    const TYPE_REVISION = 'revision';
    const TYPE_TASK_EDITED = 'task_edited';

    public function task(): BelongsTo
    {
        return $this->belongsTo(DesignTask::class, 'task_id');
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function getTypeLabelAttribute(): string
    {
        return [
            self::TYPE_STATUS_UPDATE => 'Status Update',
            self::TYPE_COMMENT => 'Comment',
            self::TYPE_FILE_UPLOAD => 'File Upload',
            self::TYPE_REVISION => 'Revision',
            self::TYPE_TASK_EDITED => 'Task Edited',
        ][$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getFileUrlsAttribute(): ?array
    {
        if ($this->type !== self::TYPE_FILE_UPLOAD || empty($this->metadata['files'])) {
            return null;
        }

        return array_map(function ($file) {
            return [
                'name' => $file['name'],
                'url' => asset('storage/' . $file['path']),
                'type' => $file['type'] ?? 'file',
            ];
        }, $this->metadata['files']);
    }

    public function getStatusChangeAttribute(): ?array
    {
        if ($this->type !== self::TYPE_STATUS_UPDATE) {
            return null;
        }

        return [
            'from' => $this->metadata['from'] ?? null,
            'to' => $this->metadata['to'] ?? null,
        ];
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeLatestFirst($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
