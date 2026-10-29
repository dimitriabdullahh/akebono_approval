<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'is_mandatory',
        'scope',
        'description',
        'order',
    ];

    protected $casts = [
        'is_mandatory' => 'boolean',
        'scope' => 'array', // JSON array: ['QA', 'PURCHASING', 'PPC']
    ];

    /**
     * Relasi: Satu document type memiliki banyak request documents
     */
    public function requestDocuments()
    {
        return $this->hasMany(RequestDocument::class);
    }
}
