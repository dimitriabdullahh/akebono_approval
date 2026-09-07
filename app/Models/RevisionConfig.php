<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RevisionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'description',
        'updated_by',
    ];

    /**
     * Relasi: User yang melakukan update terakhir
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Config keys constant
     */
    const KEY_REVISION_DEADLINE_DAYS = 'revision_deadline_days';
    const KEY_REMINDER_DAYS_BEFORE = 'reminder_days_before';
}
