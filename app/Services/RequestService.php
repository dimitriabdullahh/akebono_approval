<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\DocumentType;
use App\Models\RequestDocument;
use App\Models\RequestPart;
use Illuminate\Support\Str;

class RequestService
{
    /**
     * Buat document request baru (deprecated - gunakan createRequestWithParts)
     */
    public function createRequest(array $data, int $userId): DocumentRequest
    {
        // Generate unique access token
        $accessToken = Str::random(64);

        // Create document request
        $request = DocumentRequest::create([
            'supplier_id' => $data['supplier_id'],
            'model' => $data['model'],
            'part_name' => $data['part_name'],
            'part_number' => $data['part_number'],
            'due_date' => $data['due_date'],
            'access_token' => $accessToken,
            'created_by' => $userId,
        ]);

        // Create placeholder request documents for all document types
        $documentTypes = DocumentType::all();
        foreach ($documentTypes as $docType) {
            RequestDocument::create([
                'document_request_id' => $request->id,
                'document_type_id' => $docType->id,
                'status' => 'PENDING',
            ]);
        }

        return $request->load(['supplier', 'requestDocuments.documentType']);
    }

    /**
     * Buat document request baru dengan multiple parts
     */
    public function createRequestWithParts(array $data, int $userId): DocumentRequest
    {
        // Generate unique access token
        $accessToken = Str::random(64);

        // Create document request
        $request = DocumentRequest::create([
            'supplier_id' => $data['supplier_id'],
            'model' => $data['model'],
            'due_date' => $data['due_date'],
            'access_token' => $accessToken,
            'created_by' => $userId,
        ]);

        // Get ONLY selected document types (not all 16)
        $selectedDocumentIds = $data['selected_documents'] ?? [];
        $documentTypes = DocumentType::whereIn('id', $selectedDocumentIds)->get();

        // Create parts and their documents
        foreach ($data['parts'] as $partData) {
            $part = RequestPart::create([
                'document_request_id' => $request->id,
                'part_name' => $partData['part_name'],
                'part_number' => $partData['part_number'],
            ]);

            // Create ONLY SELECTED document placeholders for each part
            foreach ($documentTypes as $docType) {
                RequestDocument::create([
                    'document_request_id' => $request->id,
                    'request_part_id' => $part->id,
                    'document_type_id' => $docType->id,
                    'status' => 'PENDING',
                ]);
            }
        }

        return $request->load(['supplier', 'requestDocuments.documentType', 'requestParts']);
    }

    /**
     * Get document request by access token
     */
    public function getByAccessToken(string $token): ?DocumentRequest
    {
        return DocumentRequest::where('access_token', $token)
            ->with(['supplier', 'requestDocuments.documentType', 'requestDocuments.histories'])
            ->first();
    }

    /**
     * Get all requests for admin (with filters)
     */
    public function getAllRequests(array $filters = [])
    {
        $query = DocumentRequest::with(['supplier', 'requestDocuments.documentType', 'creator'])
            ->orderByDesc('created_at');

        if (!empty($filters['supplier_id'])) {
            $query->where('supplier_id', $filters['supplier_id']);
        }

        if (!empty($filters['status'])) {
            $query->whereHas('requestDocuments', function ($q) {
                $q->where('status', request('status'));
            });
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('due_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('due_date', '<=', $filters['date_to']);
        }

        return $query->paginate(15);
    }

    /**
     * Get document request by ID
     */
    public function getById(int $id): ?DocumentRequest
    {
        return DocumentRequest::with([
            'supplier',
            'requestDocuments.documentType',
            'requestDocuments.histories',
            'requestDocuments.reviewer',
            'notificationLogs',
            'creator'
        ])->find($id);
    }

    /**
     * Resend request letter email
     */
    public function resendRequestLetter(int $requestId): bool
    {
        $request = $this->getById($requestId);
        if (!$request) {
            return false;
        }

        // Dispatch job untuk mengirim email
        \App\Jobs\SendRequestLetterJob::dispatch($request);

        return true;
    }
}
