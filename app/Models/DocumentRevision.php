<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentRevision extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_document_id',
        'team',
        'user_id',
        'note',
        'revised_at',
        'resubmitted_at',
    ];

    protected $dates = [
        'revised_at',
        'resubmitted_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Revision milik satu request document
     */
    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relasi: User yang memberikan revisi
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get label team
     */
    public function getTeamLabelAttribute(): string
    {
        return match($this->team) {
            'qa' => 'Quality Assurance',
            'purchasing' => 'Purchasing',
            'ppc' => 'Production Planning & Control',
            default => ucfirst($this->team ?? 'Unknown'),
        };
    }

    /**
     * Check apakah revisi sudah diresubmit
     */
    public function isResubmitted(): bool
    {
        return !is_null($this->resubmitted_at);
    }
}
