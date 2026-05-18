<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmployeeDocument extends Model
{
    protected $fillable = [
        'employee_id', 'uploaded_by', 'document_type', 'title',
        'file_path', 'file_name', 'mime_type', 'file_size', 'notes', 'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'file_size'   => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function getIsExpiringSoonAttribute(): bool
    {
        return $this->expiry_date && !$this->expiry_date->isPast()
            && $this->expiry_date->diffInDays(now()) <= 30;
    }

    public function getIconAttribute(): string
    {
        return match(true) {
            str_contains($this->mime_type ?? '', 'pdf')   => 'fa-file-pdf text-danger',
            str_contains($this->mime_type ?? '', 'image') => 'fa-file-image text-info',
            str_contains($this->mime_type ?? '', 'word')  => 'fa-file-word text-primary',
            str_contains($this->mime_type ?? '', 'excel') => 'fa-file-excel text-success',
            default => 'fa-file-alt text-secondary',
        };
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }

    public static function typeLabel(string $type): string
    {
        return match($type) {
            'national_id'  => 'National ID',
            'passport'     => 'Passport',
            'contract'     => 'Contract',
            'certificate'  => 'Certificate',
            'insurance'    => 'Insurance',
            'bank_letter'  => 'Bank Letter',
            'nssf'         => 'NSSF Card',
            'nhif'         => 'NHIF Card',
            default        => 'Other',
        };
    }
}
