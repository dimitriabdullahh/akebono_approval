<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InspectionStandardDrawing extends Model
{
    use HasFactory;

    protected $fillable = [
        'inspection_standard_id',
        'drawing_name',
        'drawing_no',
        'revision',
        'description',
        'file_path',
        'original_filename',
        'file_type',
        'file_size',
        'sequence_number',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Belongs to InspectionStandard
     */
    public function inspectionStandard(): BelongsTo
    {
        return $this->belongsTo(InspectionStandard::class);
    }

    /**
     * Get file size in human readable format
     */
    public function getFileSizeFormattedAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if file is PDF
     */
    public function isPdf(): bool
    {
        return $this->file_type === 'pdf';
    }

    /**
     * Get file icon class based on type
     */
    public function getFileIconAttribute(): string
    {
        return match ($this->file_type) {
            'pdf' => 'ti-file-pdf',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'ti-file-image',
            default => 'ti-file',
        };
    }
}
