<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentHistory extends Model
{
    use HasFactory;

    // Tabel append-only, tidak boleh di-update/delete
    public $timestamps = false;

    protected $fillable = [
        'request_document_id',
        'action',
        'actor_id',
        'actor_name',
        'note',
        'created_at',
    ];

    protected $dates = [
        'created_at',
    ];

    /**
     * Relasi: Banyak histories milik satu request document
     */
    public function requestDocument()
    {
        return $this->belongsTo(RequestDocument::class);
    }

    /**
     * Relasi: User (actor)
     */
    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }

    /**
     * Override untuk mencegah update
     */
    public function update(array $attributes = [], array $options = [])
    {
        throw new \Exception('Document History is immutable. Cannot update records.');
    }

    /**
     * Override untuk mencegah delete
     */
    public function delete()
    {
        throw new \Exception('Document History is immutable. Cannot delete records.');
    }
}
