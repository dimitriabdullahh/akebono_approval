<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_request_id',
        'type',
        'recipient',
        'subject',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $dates = [
        'sent_at',
        'created_at',
        'updated_at',
    ];

    /**
     * Relasi: Banyak notification logs milik satu document request
     */
    public function documentRequest()
    {
        return $this->belongsTo(DocumentRequest::class);
    }

    /**
     * Notification types constant
     */
    const TYPE_REQUEST_LETTER = 'REQUEST_LETTER';
    const TYPE_DOCS_SUBMITTED = 'DOCS_SUBMITTED';
    const TYPE_REVISION_REQUESTED = 'REVISION_REQUESTED';
    const TYPE_REVISION_REMINDER = 'REVISION_REMINDER';
    const TYPE_APPROVAL_COMPLETED = 'APPROVAL_COMPLETED';

    const STATUS_SENT = 'SENT';
    const STATUS_FAILED = 'FAILED';
}
