<?php

namespace App\Http\Controllers;

use App\Models\RequestDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentPreviewController extends Controller
{
    /**
     * Show universal document preview page
     * GET /admin/document/{requestDocumentId}/preview
     */
    public function preview($requestDocumentId)
    {
        $requestDocument = RequestDocument::with([
            'documentType',
            'documentRequest.supplier',
            'requestPart',
            'inspectionStandard',
            'packagingStandard',
            'partSubmissionWarrant',
            'msaGageRr',
            'approvals.employee',
            'approvals.user',
            'histories.actor',
        ])->findOrFail($requestDocumentId);

        // Get document type info
        $documentType = $requestDocument->documentType->name ?? 'Document';
        $docCode = $requestDocument->documentType->code ?? '';
        $isMandatory = $requestDocument->documentType->is_mandatory ?? false;

        // Get supplier info
        $supplierName = $requestDocument->documentRequest->supplier->name ?? 'Unknown Supplier';

        // Get part info
        $partInfo = null;
        if ($requestDocument->requestPart) {
            $partInfo = $requestDocument->requestPart->part_number . ' - ' . $requestDocument->requestPart->part_name;
        }

        // Determine PDF URL based on document type
        $pdfUrl = $this->getPdfUrl($requestDocument, $docCode);

        // All submitted documents have review tab
        $hasReviewTab = in_array($requestDocument->status, ['SUBMITTED', 'PENDING_REVIEW', 'APPROVED', 'REJECTED']);
        
        // Check which tab should be active - ensure it's always a string
        $activeTab = (string) session('activeTab', 'preview'); // Default to preview tab, cast to string
        if (!in_array($activeTab, ['preview', 'review'])) {
            $activeTab = 'preview'; // Fallback to preview if invalid
        }

        // Get user info for review form
        $user = auth()->guard('employee')->check() 
            ? auth()->guard('employee')->user() 
            : auth()->user();
        $userScope = $user ? $user->getReviewScope() : null;
        
        $isChecker = false;
        $isApprover = false;
        $employee = null;
        
        if (auth()->guard('employee')->check()) {
            $employee = auth()->guard('employee')->user();
            $isChecker = $employee->isChecker();
            $isApprover = $employee->isApprover();
        }

        return view('admin.document-preview', compact(
            'requestDocument',
            'documentType',
            'isMandatory',
            'supplierName',
            'partInfo',
            'pdfUrl',
            'hasReviewTab',
            'activeTab',
            'userScope',
            'isChecker',
            'isApprover',
            'employee'
        ));
    }

    /**
     * Get PDF URL for document
     */
    private function getPdfUrl(RequestDocument $requestDocument, string $docCode): string
    {
        // Form-based documents - use admin routes
        if ($docCode === 'IS' && $requestDocument->inspectionStandard) {
            return route('inspection-standard.pdf', $requestDocument->inspectionStandard->id);
        }
        
        if ($docCode === 'PACKAGING_STANDARD' && $requestDocument->packagingStandard) {
            return route('admin.forms.ps.pdf', ['psId' => $requestDocument->packagingStandard->id]);
        }
        
        if ($docCode === 'PSW' && $requestDocument->partSubmissionWarrant) {
            return route('admin.forms.psw.pdf', ['pswId' => $requestDocument->partSubmissionWarrant->id]);
        }
        
        if ($docCode === 'MSA' && $requestDocument->msaGageRr) {
            return route('admin.forms.msa.pdf', ['msaId' => $requestDocument->msaGageRr->id]);
        }

        // Regular upload documents - use serve route
        if ($requestDocument->file_path) {
            return route('admin.document.serve', $requestDocument->id);
        }

        // No file available
        abort(404, 'Document not yet submitted or file not available');
    }

    /**
     * Serve document file from storage
     * GET /admin/document/{requestDocumentId}/serve
     */
    public function serve($requestDocumentId)
    {
        $requestDocument = RequestDocument::findOrFail($requestDocumentId);

        if (!$requestDocument->file_path) {
            abort(404, 'Document file not found');
        }

        // Try multiple storage disks
        $filePath = null;
        $mimeType = null;
        
        // Check public disk first
        if (Storage::disk('public')->exists($requestDocument->file_path)) {
            $filePath = Storage::disk('public')->path($requestDocument->file_path);
            $mimeType = Storage::disk('public')->mimeType($requestDocument->file_path);
        } 
        // Then try documents disk
        elseif (Storage::disk('documents')->exists($requestDocument->file_path)) {
            $filePath = Storage::disk('documents')->path($requestDocument->file_path);
            $mimeType = Storage::disk('documents')->mimeType($requestDocument->file_path);
        }
        // Then try local disk as fallback
        elseif (Storage::disk('local')->exists($requestDocument->file_path)) {
            $filePath = Storage::disk('local')->path($requestDocument->file_path);
            $mimeType = Storage::disk('local')->mimeType($requestDocument->file_path);
        }

        if (!$filePath || !file_exists($filePath)) {
            \Log::error('Document file not found in any storage disk', [
                'request_document_id' => $requestDocumentId,
                'file_path' => $requestDocument->file_path,
            ]);
            abort(404, 'Document file not found in storage');
        }

        return response()->file($filePath, [
            'Content-Type' => $mimeType ?? 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . ($requestDocument->original_filename ?? basename($requestDocument->file_path)) . '"'
        ]);
    }
}
