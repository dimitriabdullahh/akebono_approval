<?php

namespace App\Http\Controllers;

use App\Models\CheckerSignature;
use Illuminate\Http\Request;

class SignatureVerificationController extends Controller
{
    /**
     * Verify digital signature via QR code token
     * Public access - no authentication required
     *
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function verify(string $token)
    {
        // Find signature by token
        $signature = CheckerSignature::findByToken($token);

        if (!$signature) {
            return view('signature.verify', [
                'verified' => false,
                'error' => 'Invalid or expired signature token',
            ]);
        }

        // Load relationships
        $signature->load(['requestDocument.documentType', 'employee']);

        // Record verification
        $signature->recordVerification();

        return view('signature.verify', [
            'verified' => true,
            'signature' => $signature,
        ]);
    }

    /**
     * Verify signature by verification code (manual entry)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function verifyByCode(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|size:12',
        ]);

        $signature = CheckerSignature::findByVerificationCode($validated['code']);

        if (!$signature) {
            return response()->json([
                'verified' => false,
                'message' => 'Invalid verification code',
            ], 404);
        }

        // Load relationships
        $signature->load(['requestDocument.documentType', 'employee']);

        // Record verification
        $signature->recordVerification();

        return response()->json([
            'verified' => true,
            'signature' => [
                'type' => $signature->signature_type_label,
                'employee_name' => $signature->employee_name,
                'employee_npk' => $signature->employee_npk,
                'division' => $signature->division,
                'role' => $signature->role,
                'document_type' => $signature->document_type,
                'document_reference' => $signature->document_reference,
                'action_date' => $signature->action_at->format('d M Y H:i'),
            ],
        ]);
    }
}
