<?php

namespace App\Http\Controllers;

use App\Models\ApprovalBlastToken;
use App\Models\DocumentSignature;
use App\Services\SignatureQrService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalTokenController extends Controller
{
    protected SignatureQrService $qrService;

    public function __construct(SignatureQrService $qrService)
    {
        $this->qrService = $qrService;
    }

    /**
     * Show approval page for a token
     */
    public function show(string $token, Request $request)
    {
        // Find token by plain token
        $approvalToken = ApprovalBlastToken::findByPlainToken($token);

        if (!$approvalToken) {
            return view('approval.not-found', [
                'message' => 'Link approval tidak ditemukan atau sudah kedaluwarsa.',
            ]);
        }

        // Track access
        $approvalToken->trackAccess(
            $request->ip(),
            $request->userAgent()
        );

        // Validate token
        $validation = $approvalToken->getValidationStatus();
        if (!$validation['valid']) {
            return view('approval.invalid', [
                'token' => $approvalToken,
                'message' => $validation['reason'],
            ]);
        }

        // Load related data
        $requestDocument = $approvalToken->requestDocument()->with([
            'documentType',
            'documentRequest.supplier',
        ])->firstOrFail();

        $user = $approvalToken->user;
        $division = $approvalToken->division;

        // Check if already approved by this division
        $existingSignature = DocumentSignature::where('request_document_id', $requestDocument->id)
            ->where('role_type', $division)
            ->first();

        // Get required divisions for this document type
        $requiredDivisions = config(
            'approval_blast.document_divisions.' . $requestDocument->documentType->code,
            []
        );

        // Get status of all divisions
        $divisionsStatus = [];
        foreach ($requiredDivisions as $div) {
            $sig = DocumentSignature::where('request_document_id', $requestDocument->id)
                ->where('role_type', $div)
                ->first();
            
            $divisionsStatus[$div] = [
                'approved' => $sig !== null,
                'approver_name' => $sig?->name,
                'signed_at' => $sig?->signed_at,
            ];
        }

        return view('approval.show', [
            'requestDocument' => $requestDocument,
            'approvalToken' => $approvalToken,
            'user' => $user,
            'division' => $division,
            'existingSignature' => $existingSignature,
            'requiredDivisions' => $requiredDivisions,
            'divisionsStatus' => $divisionsStatus,
        ]);
    }

    /**
     * Handle approval action
     */
    public function approve(string $token, Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Find token
        $approvalToken = ApprovalBlastToken::findByPlainToken($token);

        if (!$approvalToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 404);
        }

        // Validate token
        $validation = $approvalToken->getValidationStatus();
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['reason'],
            ], 422);
        }

        try {
            DB::beginTransaction();

            $requestDocument = $approvalToken->requestDocument;
            $user = $approvalToken->user;
            $division = $approvalToken->division;

            // Check if already approved
            $existingSignature = DocumentSignature::where('request_document_id', $requestDocument->id)
                ->where('role_type', $division)
                ->first();

            if ($existingSignature) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Dokumen sudah disetujui oleh ' . $existingSignature->name . '.',
                ], 422);
            }

            // Create signature record
            $signature = DocumentSignature::create([
                'request_document_id' => $requestDocument->id,
                'role_type' => $division,
                'employee_id' => $user->employee?->id,
                'npk' => $user->npk,
                'name' => $user->name,
                'role_label' => config('approval_blast.signature_role_labels.' . $division . '.role_label'),
                'notes' => $validated['notes'] ?? null,
                'signed_at' => now(),
            ]);

            // Mark token as used
            $approvalToken->markAsUsed();

            // Check if all required divisions have approved
            $requiredDivisions = config(
                'approval_blast.document_divisions.' . $requestDocument->documentType->code,
                []
            );

            $allApproved = true;
            foreach ($requiredDivisions as $div) {
                $sig = DocumentSignature::where('request_document_id', $requestDocument->id)
                    ->where('role_type', $div)
                    ->first();
                
                if (!$sig) {
                    $allApproved = false;
                    break;
                }
            }

            // Update document status if all approved
            if ($allApproved && count($requiredDivisions) > 0) {
                $requestDocument->update([
                    'status' => 'APPROVED',
                    'approved_at' => now(),
                ]);

                Log::info('Document fully approved', [
                    'request_document_id' => $requestDocument->id,
                    'divisions' => $requiredDivisions,
                ]);

                // Fire notification events if needed
                event(new \App\Events\DocumentApproved($requestDocument));
            }

            DB::commit();

            // Generate QR code for response
            try {
                $qrCode = $this->qrService->generate(
                    $user->npk,
                    $user->name,
                    config('approval_blast.signature_role_labels.' . $division . '.role_label')
                );
            } catch (\Exception $e) {
                Log::error('Failed to generate QR code', [
                    'error' => $e->getMessage(),
                ]);
                $qrCode = null;
            }

            return response()->json([
                'success' => true,
                'message' => 'Dokumen telah disetujui oleh ' . $user->name . '.',
                'signature' => [
                    'name' => $signature->name,
                    'npk' => $signature->npk,
                    'role_label' => $signature->role_label,
                    'signed_at' => $signature->signed_at->format('d M Y H:i'),
                    'qr_code' => $qrCode,
                ],
                'all_approved' => $allApproved,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Approval process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses persetujuan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle revision request
     */
    public function revise(string $token, Request $request)
    {
        // Validate input
        $validated = $request->validate([
            'notes' => 'required|string|min:10|max:2000',
        ]);

        // Find token
        $approvalToken = ApprovalBlastToken::findByPlainToken($token);

        if (!$approvalToken) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid.',
            ], 404);
        }

        // Validate token
        $validation = $approvalToken->getValidationStatus();
        if (!$validation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $validation['reason'],
            ], 422);
        }

        try {
            DB::beginTransaction();

            $requestDocument = $approvalToken->requestDocument;
            $user = $approvalToken->user;
            $division = $approvalToken->division;

            // Update document status to REVISE
            $requestDocument->update([
                'status' => 'REVISE',
                'revision_notes' => $validated['notes'],
                'revised_by_user_id' => $user->id,
                'revised_at' => now(),
            ]);

            // Invalidate all tokens for this document
            ApprovalBlastToken::invalidateForDocument($requestDocument->id);

            // Invalidate signatures for this division
            DocumentSignature::where('request_document_id', $requestDocument->id)
                ->where('role_type', $division)
                ->delete();

            // Mark token as used
            $approvalToken->markAsUsed();

            DB::commit();

            Log::info('Revision requested', [
                'request_document_id' => $requestDocument->id,
                'requested_by_user' => $user->name,
                'division' => $division,
            ]);

            // Fire notification event
            event(new \App\Events\DocumentRevisionRequested(
                $requestDocument,
                $user,
                $division,
                $validated['notes']
            ));

            return response()->json([
                'success' => true,
                'message' => 'Permintaan revisi telah dikirim ke supplier.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Revision request failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses permintaan revisi: ' . $e->getMessage(),
            ], 500);
        }
    }
}
