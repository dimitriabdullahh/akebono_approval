<?php

namespace App\Services;

use App\Models\DocumentHistory;
use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentUploadService
{
    /**
     * Validasi file sebelum upload
     */
    public function validateFile(UploadedFile $file): array
    {
        $maxSizeMB = (int) \App\Models\RevisionConfig::where('key', 'max_file_size_mb')->first()?->value ?? 10;
        $allowedTypes = explode(',', \App\Models\RevisionConfig::where('key', 'allowed_file_types')->first()?->value ?? 'pdf,jpg,jpeg,png');

        $errors = [];

        // Cek ukuran file
        if ($file->getSize() > $maxSizeMB * 1024 * 1024) {
            $errors[] = "Ukuran file tidak boleh melebihi {$maxSizeMB}MB";
        }

        // Cek tipe file
        $mimeType = $file->getMimeType();
        $ext = strtolower($file->getClientOriginalExtension());

        if (!in_array($ext, $allowedTypes)) {
            $errors[] = "Tipe file {$ext} tidak diizinkan. Format yang diizinkan: " . implode(', ', $allowedTypes);
        }

        return $errors;
    }

    /**
     * Upload dokumen supplier
     */
    public function uploadDocument(
        RequestDocument $requestDocument,
        UploadedFile $file,
        string $portalToken
    ): bool {
        // Validasi file
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            throw new \Exception(implode('; ', $errors));
        }

        // Store file
        $relativePath = "documents/{$portalToken}/{$requestDocument->id}";
        $storagePath = Storage::disk('local')->putFile($relativePath, $file);

        // Update request document
        $requestDocument->update([
            'file_path' => $storagePath,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'SUBMITTED',
            'uploaded_at' => now(),
        ]);

        // Catat history
        DocumentHistory::create([
            'request_document_id' => $requestDocument->id,
            'action' => 'UPLOAD',
            'actor_name' => 'Supplier',
            'note' => "File '{$file->getClientOriginalName()}' berhasil diunggah",
        ]);

        return true;
    }

    /**
     * Re-upload dokumen (untuk revisi)
     */
    public function reuploadDocument(
        RequestDocument $requestDocument,
        UploadedFile $file,
        string $portalToken
    ): bool {
        // Validasi status harus REVISE
        if ($requestDocument->status !== 'REVISE') {
            throw new \Exception('Dokumen tidak dalam status REVISE');
        }

        // Validasi file
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            throw new \Exception(implode('; ', $errors));
        }

        // Hapus file lama jika ada
        if ($requestDocument->file_path) {
            Storage::disk('local')->delete($requestDocument->file_path);
        }

        // Store file baru
        $relativePath = "documents/{$portalToken}/{$requestDocument->id}";
        $storagePath = Storage::disk('local')->putFile($relativePath, $file);

        // Update request document
        $requestDocument->update([
            'file_path' => $storagePath,
            'original_filename' => $file->getClientOriginalName(),
            'status' => 'SUBMITTED',
            'uploaded_at' => now(),
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        // Catat history
        DocumentHistory::create([
            'request_document_id' => $requestDocument->id,
            'action' => 'REUPLOAD',
            'actor_name' => 'Supplier',
            'note' => "File revisi '{$file->getClientOriginalName()}' berhasil diunggah",
        ]);

        return true;
    }

    /**
     * Download dokumen approved
     */
    public function downloadDocument(RequestDocument $requestDocument): string
    {
        if ($requestDocument->status !== 'APPROVED') {
            throw new \Exception('Hanya dokumen yang sudah APPROVED dapat diunduh');
        }

        if (!$requestDocument->file_path) {
            throw new \Exception('File tidak ditemukan');
        }

        return $requestDocument->file_path;
    }

    /**
     * Check apakah semua dokumen mandatory sudah SUBMITTED
     * Support untuk multi-part request
     */
    public function allMandatoryDocumentsSubmitted(DocumentRequest $request): bool
    {
        // Untuk multi-part request, check semua parts
        if ($request->hasMultipleParts()) {
            foreach ($request->requestParts as $part) {
                $mandatoryDocs = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->get();

                foreach ($mandatoryDocs as $doc) {
                    if (!in_array($doc->status, ['SUBMITTED', 'REVISE', 'APPROVED'])) {
                        return false;
                    }
                }
            }
            return true;
        }
        
        // Untuk request tanpa parts (backward compatibility)
        $mandatoryDocs = $request->requestDocuments()
            ->whereHas('documentType', function ($q) {
                $q->where('is_mandatory', true);
            })
            ->get();

        foreach ($mandatoryDocs as $doc) {
            if (!in_array($doc->status, ['SUBMITTED', 'REVISE', 'APPROVED'])) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Get missing mandatory documents untuk semua parts
     * Return array of missing document info grouped by part
     */
    public function getMissingMandatoryDocuments(DocumentRequest $request): array
    {
        $missing = [];
        
        if ($request->hasMultipleParts()) {
            foreach ($request->requestParts as $part) {
                $partMissing = [];
                
                $mandatoryDocs = $part->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->with('documentType')
                    ->get();

                foreach ($mandatoryDocs as $doc) {
                    if ($doc->status === 'PENDING' || empty($doc->file_path)) {
                        $partMissing[] = $doc->documentType->name;
                    }
                }
                
                if (!empty($partMissing)) {
                    $missing[] = [
                        'part_name' => $part->part_name,
                        'part_number' => $part->part_number,
                        'documents' => $partMissing,
                    ];
                }
            }
        } else {
            // Backward compatibility
            $partMissing = [];
            
            $mandatoryDocs = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->with('documentType')
                ->get();

            foreach ($mandatoryDocs as $doc) {
                if ($doc->status === 'PENDING' || empty($doc->file_path)) {
                    $partMissing[] = $doc->documentType->name;
                }
            }
            
            if (!empty($partMissing)) {
                $missing[] = [
                    'part_name' => 'Main Part',
                    'part_number' => $request->part_number ?? '-',
                    'documents' => $partMissing,
                ];
            }
        }
        
        return $missing;
    }
}
