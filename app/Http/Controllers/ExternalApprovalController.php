<?php

namespace App\Http\Controllers;

use App\Models\ApprovalToken;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ExternalApprovalController extends Controller
{
    private ApprovalService $approvalService;

    public function __construct(ApprovalService $approvalService)
    {
        $this->approvalService = $approvalService;
    }

    /**
     * Show external approval review page (no login required)
     * 
     * @param string $token Plain token from email
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show(string $token)
    {
        try {
            // Find token
            $approvalToken = ApprovalToken::findByPlainToken($token);
            
            if (!$approvalToken) {
                Log::warning('[ExternalApproval] Token not found', ['token_hash' => substr(hash('sha256', $token), 0, 16)]);
                return view('external-approval.error', [
                    'title' => 'Invalid Token',
                    'message' => 'The approval link is invalid or has been removed.',
                    'reason' => 'Token not found',
                ]);
            }
            
            // Track access
            $approvalToken->trackAccess(
                request()->ip(),
                request()->userAgent()
            );
            
            // Validate token
            $validation = $approvalToken->validate();
            if (!$validation['valid']) {
                Log::warning('[ExternalApproval] Token validation failed', [
                    'token_id' => $approvalToken->id,
                    'reason' => $validation['reason'],
                ]);
                
                return view('external-approval.error', [
                    'title' => 'Token Invalid',
                    'message' => $validation['reason'],
                    'reason' => $validation['reason'],
                    'token' => $approvalToken,
                ]);
            }
            
            // Load approval and document data
            $approval = $approvalToken->documentApproval;
            $approval->load([
                'requestDocument.documentRequest.supplier',
                'requestDocument.documentType',
                'requestDocument.requestPart',
                'requestDocument.partSubmissionWarrant',
                'requestDocument.packagingStandard',
            ]);
            
            $requestDocument = $approval->requestDocument;
            $documentType = $requestDocument->documentType->code;
            
            // Prepare document data
            $documentData = $this->prepareDocumentData($requestDocument, $documentType);
            
            // Get other approvals status
            $approvalStatus = $this->approvalService->getApprovalStatus($requestDocument);
            
            Log::info('[ExternalApproval] Review page accessed', [
                'token_id' => $approvalToken->id,
                'approval_id' => $approval->id,
                'division' => $approval->division,
                'ip' => request()->ip(),
            ]);
            
            return view('external-approval.review', compact(
                'approvalToken',
                'approval',
                'requestDocument',
                'documentData',
                'documentType',
                'approvalStatus'
            ));
            
        } catch (\Exception $e) {
            Log::error('[ExternalApproval] Error showing review page', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return view('external-approval.error', [
                'title' => 'Error',
                'message' => 'An error occurred while loading the approval page.',
                'reason' => 'System error',
            ]);
        }
    }

    /**
     * Process approval decision
     * 
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function approve(Request $request, string $token)
    {
        try {
            // Find token
            $approvalToken = ApprovalToken::findByPlainToken($token);
            
            if (!$approvalToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token',
                ], 404);
            }
            
            // Validate input
            $validator = Validator::make($request->all(), [
                'comment' => 'nullable|string|max:1000',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Track access
            $approvalToken->trackAccess(
                $request->ip(),
                $request->userAgent()
            );
            
            // Process approval
            $result = $this->approvalService->processTokenApproval(
                $approvalToken,
                'APPROVED',
                [
                    'comment' => $request->input('comment'),
                    'note' => $request->input('comment'),
                ]
            );
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
            
            Log::info('[ExternalApproval] Document approved', [
                'token_id' => $approvalToken->id,
                'approval_id' => $approvalToken->document_approval_id,
                'division' => $approvalToken->division,
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Document approved successfully',
                'redirect' => route('external-approval.success', ['token' => $token]),
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ExternalApproval] Error processing approval', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while processing your approval',
            ], 500);
        }
    }

    /**
     * Process revision request
     * 
     * @param Request $request
     * @param string $token
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function requestRevision(Request $request, string $token)
    {
        try {
            // Find token
            $approvalToken = ApprovalToken::findByPlainToken($token);
            
            if (!$approvalToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid token',
                ], 404);
            }
            
            // Validate input - reason is REQUIRED for revision
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|min:10|max:1000',
            ], [
                'reason.required' => 'Please provide a reason for requesting revision',
                'reason.min' => 'Reason must be at least 10 characters',
                'reason.max' => 'Reason cannot exceed 1000 characters',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }
            
            // Track access
            $approvalToken->trackAccess(
                $request->ip(),
                $request->userAgent()
            );
            
            // Process revision request
            $result = $this->approvalService->requestRevisionViaToken(
                $approvalToken,
                $request->input('reason')
            );
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 400);
            }
            
            Log::info('[ExternalApproval] Revision requested', [
                'token_id' => $approvalToken->id,
                'approval_id' => $approvalToken->document_approval_id,
                'division' => $approvalToken->division,
                'reason' => $request->input('reason'),
                'ip' => $request->ip(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Revision requested successfully',
                'redirect' => route('external-approval.success', ['token' => $token]),
            ]);
            
        } catch (\Exception $e) {
            Log::error('[ExternalApproval] Error requesting revision', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while requesting revision',
            ], 500);
        }
    }

    /**
     * Show success page after approval/revision
     * 
     * @param string $token
     * @return \Illuminate\View\View
     */
    public function success(string $token)
    {
        try {
            $approvalToken = ApprovalToken::findByPlainToken($token);
            
            if (!$approvalToken) {
                return view('external-approval.error', [
                    'title' => 'Token Not Found',
                    'message' => 'The approval link is invalid.',
                    'reason' => 'Token not found',
                ]);
            }
            
            $approval = $approvalToken->documentApproval;
            
            return view('external-approval.success', compact('approvalToken', 'approval'));
            
        } catch (\Exception $e) {
            Log::error('[ExternalApproval] Error showing success page', [
                'error' => $e->getMessage(),
            ]);
            
            return view('external-approval.error', [
                'title' => 'Error',
                'message' => 'An error occurred.',
                'reason' => 'System error',
            ]);
        }
    }

    /**
     * Download or view document PDF
     * 
     * @param string $token
     * @param string $action 'view' or 'download'
     * @return \Illuminate\Http\Response
     */
    public function viewDocument(string $token, string $action = 'view')
    {
        try {
            $approvalToken = ApprovalToken::findByPlainToken($token);
            
            if (!$approvalToken || !$approvalToken->isValid()) {
                abort(403, 'Invalid or expired token');
            }
            
            $requestDocument = $approvalToken->requestDocument;
            $documentType = $requestDocument->documentType->code;
            
            // Track access
            $approvalToken->trackAccess(request()->ip(), request()->userAgent());
            
            // Redirect to appropriate PDF viewer based on document type
            if ($documentType === 'PSW') {
                $psw = $requestDocument->partSubmissionWarrant;
                if (!$psw) {
                    abort(404, 'PSW document not found');
                }
                
                if ($action === 'download') {
                    return redirect()->route('psw.download', ['pswId' => $psw->id]);
                }
                return redirect()->route('psw.pdf', ['pswId' => $psw->id]);
                
            } elseif ($documentType === 'PACKAGING_STANDARD') {
                $ps = $requestDocument->packagingStandard;
                if (!$ps) {
                    abort(404, 'PS document not found');
                }
                
                if ($action === 'download') {
                    return redirect()->route('ps.download', ['psId' => $ps->id]);
                }
                return redirect()->route('ps.pdf', ['psId' => $ps->id]);
                
            } else {
                abort(404, 'Document type not supported for external approval');
            }
            
        } catch (\Exception $e) {
            Log::error('[ExternalApproval] Error viewing document', [
                'error' => $e->getMessage(),
            ]);
            abort(500, 'Error loading document');
        }
    }

    /**
     * Prepare document data for display
     * 
     * @param \App\Models\RequestDocument $requestDocument
     * @param string $documentType
     * @return array
     */
    protected function prepareDocumentData($requestDocument, string $documentType): array
    {
        $data = [
            'id' => $requestDocument->id,
            'status' => $requestDocument->status,
            'submitted_at' => $requestDocument->submitted_at,
            'supplier' => $requestDocument->documentRequest->supplier,
            'model' => $requestDocument->documentRequest->model,
            'due_date' => $requestDocument->documentRequest->due_date,
        ];
        
        // Add part information
        if ($requestDocument->requestPart) {
            $data['part_name'] = $requestDocument->requestPart->part_name;
            $data['part_number'] = $requestDocument->requestPart->part_number;
        }
        
        // Add document-specific data
        if ($documentType === 'PSW' && $requestDocument->partSubmissionWarrant) {
            $psw = $requestDocument->partSubmissionWarrant;
            $data['psw'] = [
                'id' => $psw->id,
                'part_name' => $psw->part_name,
                'part_number' => $psw->part_number,
                'supplier_name' => $psw->supplier_name,
                'supplier_code' => $psw->supplier_code,
                'status' => $psw->status,
                'submitted_at' => $psw->submitted_at,
                'pdf_url' => route('psw.pdf', ['pswId' => $psw->id]),
            ];
        } elseif ($documentType === 'PACKAGING_STANDARD' && $requestDocument->packagingStandard) {
            $ps = $requestDocument->packagingStandard;
            $data['ps'] = [
                'id' => $ps->id,
                'document_no' => $ps->document_no,
                'part_no' => $ps->part_no,
                'part_name' => $ps->part_name,
                'packaging_type' => $ps->packaging_type,
                'packaging_size_mm' => $ps->packaging_size_mm,
                'packaging_lot_pcs_box' => $ps->packaging_lot_pcs_box,
                'status' => $ps->status,
                'submitted_at' => $ps->submitted_at,
                'pdf_url' => route('ps.pdf', ['psId' => $ps->id]),
            ];
        }
        
        return $data;
    }
}
