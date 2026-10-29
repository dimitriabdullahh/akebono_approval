<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SignedUrlController;
use App\Http\Controllers\MsaGageRrController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->name('login.post')->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
// Handle GET requests to logout (redirect to login)
Route::get('/logout', function () {
    auth()->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();
    return redirect()->route('login');
})->name('logout.get');

// Employee Authentication Routes
Route::prefix('employee')->name('employee.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\EmployeeAuthController::class, 'showLogin'])
        ->name('login')
        ->middleware('guest:employee');
    
    Route::post('/login', [\App\Http\Controllers\EmployeeAuthController::class, 'login'])
        ->name('login.post')
        ->middleware('guest:employee');
    
    Route::post('/logout', [\App\Http\Controllers\EmployeeAuthController::class, 'logout'])
        ->name('logout');
});

// Portal access via token (no login required)
Route::get('/portal/{token}', [\App\Http\Controllers\PortalController::class, 'accessViaToken'])
    ->name('portal.access');

// External Approval Routes (email-based approval without login)
Route::prefix('approval')->name('external-approval.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\ExternalApprovalController::class, 'show'])
        ->name('show');
    
    Route::post('/{token}/approve', [\App\Http\Controllers\ExternalApprovalController::class, 'approve'])
        ->name('approve');
    
    Route::post('/{token}/reject', [\App\Http\Controllers\ExternalApprovalController::class, 'requestRevision'])
        ->name('reject');
    
    Route::get('/{token}/success', [\App\Http\Controllers\ExternalApprovalController::class, 'success'])
        ->name('success');
    
    Route::get('/{token}/document/view', [\App\Http\Controllers\ExternalApprovalController::class, 'viewDocument'])
        ->name('view-document')
        ->defaults('action', 'view');
    
    Route::get('/{token}/document/download', [\App\Http\Controllers\ExternalApprovalController::class, 'viewDocument'])
        ->name('download-document')
        ->defaults('action', 'download');
});

// QR Code Signature Verification (public access)
Route::get('/signature/verify/{token}', [\App\Http\Controllers\SignatureVerificationController::class, 'verify'])
    ->name('signature.verify');

// QR Code Image Generation
Route::get('/signature/qr/{token}', function($token) {
    $qrService = app(\App\Services\QRCodeService::class);
    $verificationUrl = route('signature.verify', ['token' => $token]);
    
    try {
        $qrCodeBase64 = $qrService->generateBase64($verificationUrl, 200);
        
        // Return as image
        $imageData = base64_decode(str_replace('data:image/png;base64,', '', $qrCodeBase64));
        return response($imageData)->header('Content-Type', 'image/png');
    } catch (\Exception $e) {
        abort(500, 'Failed to generate QR code');
    }
})->name('signature.qr');

// Portal routes (no auth required, token-based)
Route::prefix('supplier/upload')->name('portal.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\PortalController::class, 'show'])
        ->name('upload');
    
    Route::post('/{token}', [\App\Http\Controllers\PortalController::class, 'upload'])
        ->name('upload.post');
    
    Route::post('/{token}/document/{documentId}/reupload', [\App\Http\Controllers\PortalController::class, 'reupload'])
        ->name('reupload');
    
    Route::get('/{token}/document/{documentId}/download', [\App\Http\Controllers\PortalController::class, 'download'])
        ->name('download');
    
    Route::get('/{token}/document/{documentId}/preview', [\App\Http\Controllers\PortalController::class, 'preview'])
        ->name('preview');
    
    Route::get('/{token}/notifications', [\App\Http\Controllers\PortalController::class, 'notifications'])
        ->name('notifications');
    
    Route::post('/{token}/save-draft', [\App\Http\Controllers\PortalController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{token}/submit-all', [\App\Http\Controllers\PortalController::class, 'submitAll'])
        ->name('submit-all');
});

// Inspection Standard Form routes (token-based, no auth required)
Route::prefix('inspection-standard')->name('inspection-standard.')->group(function () {
    Route::get('/form/{token}', [\App\Http\Controllers\InspectionStandardController::class, 'showForm'])
        ->name('form');
    
    Route::get('/{inspectionStandardId}/pdf', [\App\Http\Controllers\InspectionStandardController::class, 'viewPdf'])
        ->name('pdf');
    
    Route::get('/{inspectionStandardId}/download', [\App\Http\Controllers\InspectionStandardController::class, 'downloadPdf'])
        ->name('download');
    
    // Serve drawing files
    Route::get('/drawings/{inspectionStandardId}/{filename}', [\App\Http\Controllers\InspectionStandardController::class, 'serveDrawing'])
        ->name('serve-drawing');
});

// Inspection Standard API routes (token-based, no auth required)
Route::prefix('api/is')->name('api.is.')->group(function () {
    Route::get('/form/{token}', [\App\Http\Controllers\InspectionStandardController::class, 'getForm'])
        ->name('get-form');
    
    Route::post('/{inspectionStandardId}/draft', [\App\Http\Controllers\InspectionStandardController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{inspectionStandardId}/submit', [\App\Http\Controllers\InspectionStandardController::class, 'submit'])
        ->name('submit');
    
    Route::post('/{inspectionStandardId}/upload-drawing', [\App\Http\Controllers\InspectionStandardController::class, 'uploadDrawing'])
        ->name('upload-drawing');
    
    Route::delete('/{inspectionStandardId}/drawings/{drawingId}', [\App\Http\Controllers\InspectionStandardController::class, 'deleteDrawing'])
        ->name('delete-drawing');
    
    Route::get('/{inspectionStandardId}', [\App\Http\Controllers\InspectionStandardController::class, 'show'])
        ->name('show');
});

// MSA Gage R&R routes (token-based, no auth required)
Route::get('/supplier/msa/{token}', [\App\Http\Controllers\MsaGageRrController::class, 'page'])
    ->name('supplier.msa');

Route::prefix('api/msa')->name('api.msa.')->group(function () {
    Route::get('/form/{token}', [\App\Http\Controllers\MsaGageRrController::class, 'form'])
        ->name('get-form');
    
    Route::post('/{id}/draft', [\App\Http\Controllers\MsaGageRrController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{id}/submit', [\App\Http\Controllers\MsaGageRrController::class, 'submit'])
        ->name('submit');
    
    Route::get('/{id}/pdf', [\App\Http\Controllers\MsaGageRrController::class, 'pdf'])
        ->name('pdf');
    
    Route::get('/{id}/download', [\App\Http\Controllers\MsaGageRrController::class, 'download'])
        ->name('download');
});

// Auto-login from signed URL (for email links)
Route::get('/auto-login/{userId}/{requestId}', [SignedUrlController::class, 'autoLogin'])
    ->name('auto-login')
    ->middleware('signed');

// Redirect home to login or dashboard based on role
Route::get('/', function () {
    if (auth()->check()) {
        if (auth()->user()->isSupplier()) {
            return redirect()->route('supplier.dashboard');
        }
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Public link to access document request from email
// This route is outside auth middleware to allow redirect to login
Route::get('/request/{requestId}/access', [\App\Http\Controllers\SupplierController::class, 'accessFromEmail'])
    ->name('supplier.request.access');

// Supplier Routes (Authentication required with supplier role)
Route::prefix('supplier')->name('supplier.')->middleware(['auth', 'supplier'])->group(function () {
    Route::get('/', [\App\Http\Controllers\SupplierController::class, 'dashboard'])
        ->name('dashboard');

    // TEMPORARILY DISABLED: Outstanding documents route
    // Route::get('/outstanding', [\App\Http\Controllers\SupplierController::class, 'outstandingDocuments'])
    //     ->name('outstanding');

    Route::get('/request/{requestId}', [\App\Http\Controllers\SupplierController::class, 'showRequest'])
        ->name('request');

    Route::get('/document/{request}', [\App\Http\Controllers\SupplierController::class, 'showDocumentRequest'])
        ->name('document.show');

    Route::get('/request/{requestId}/part/{partId}/documents', [\App\Http\Controllers\SupplierController::class, 'getPartDocuments'])
        ->name('part.documents');

    // Submit all documents for a part
    Route::post('/part/{partId}/submit', [\App\Http\Controllers\SupplierController::class, 'submitAllDocuments'])
        ->name('part.submit');

    Route::get('/document/{documentId}', [\App\Http\Controllers\SupplierController::class, 'showDocument'])
        ->name('document');

    Route::get('/document/{documentId}/upload', [\App\Http\Controllers\SupplierController::class, 'uploadForm'])
        ->name('upload.form');

    Route::post('/document/{documentId}/upload', [\App\Http\Controllers\SupplierController::class, 'uploadDocument'])
        ->name('upload.submit');

    Route::get('/document/{documentId}/view', [\App\Http\Controllers\SupplierController::class, 'viewDocument'])
        ->name('view');

    Route::get('/document/{documentId}/download', [\App\Http\Controllers\SupplierController::class, 'downloadDocument'])
        ->name('download');

    Route::get('/request/{requestId}/download-all', [\App\Http\Controllers\SupplierController::class, 'downloadAllApproved'])
        ->name('download.all');

    // Draft and submission routes
    Route::post('/part/{partId}/save-draft', [\App\Http\Controllers\SupplierController::class, 'saveDraft'])
        ->name('part.save-draft');

    Route::post('/part/{partId}/submit', [\App\Http\Controllers\SupplierController::class, 'submitAllDocuments'])
        ->name('part.submit');
});

// Admin Panel Routes (Authentication required - supports both employee and legacy user auth)
Route::middleware(['internal_admin'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::get('/dashboard/not-submitted', [DashboardController::class, 'notSubmitted'])
        ->name('dashboard.not_submitted');

    Route::get('/dashboard/in-progress', [DashboardController::class, 'inProgress'])
        ->name('dashboard.in_progress');

    Route::get('/dashboard/completed', [DashboardController::class, 'completed'])
        ->name('dashboard.completed');

    // Admin Request Management - Semua admin internal bisa akses, tapi hanya QA yang bisa create
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [RequestController::class, 'index'])
            ->name('index');

        Route::get('/create', [RequestController::class, 'create'])
            ->name('create'); // Controller akan check canCreateRequest()

        Route::post('/', [RequestController::class, 'store'])
            ->name('store'); // Controller akan check canCreateRequest()

        Route::get('/{documentRequest}', [RequestController::class, 'show'])
            ->name('show');

        Route::get('/{documentRequest}/review', [RequestController::class, 'review'])
            ->name('review');

        Route::post('/{documentRequest}/resend-email', [RequestController::class, 'resendEmail'])
            ->name('resend-email');
    });

    // Universal Document Preview - All document types
    Route::prefix('document')->name('admin.document.')->group(function () {
        Route::get('/{requestDocumentId}/preview', [\App\Http\Controllers\DocumentPreviewController::class, 'preview'])
            ->name('preview');
        
        Route::get('/{requestDocumentId}/serve', [\App\Http\Controllers\DocumentPreviewController::class, 'serve'])
            ->name('serve');
    });

    // Admin Review & Approval (dengan scope checking sesuai Implementation Document)
    Route::prefix('review')->name('review.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])
            ->name('index');

        Route::get('/{requestDocument}', [ReviewController::class, 'show'])
            ->name('show')
            ->middleware('scope.check');

        Route::post('/{requestDocument}/approve', [ReviewController::class, 'approve'])
            ->name('approve')
            ->middleware('scope.check');

        Route::post('/{requestDocument}/reject', [ReviewController::class, 'reject'])
            ->name('reject')
            ->middleware('scope.check');
        
        // Checker actions
        Route::post('/{requestDocument}/complete-check', [ReviewController::class, 'completeCheck'])
            ->name('complete-check')
            ->middleware('scope.check');
        
        Route::post('/{requestDocument}/checker-revision', [ReviewController::class, 'checkerRequestRevision'])
            ->name('checker-revision')
            ->middleware('scope.check');
        
        Route::post('/{requestDocument}/receive', [ReviewController::class, 'receiveDocument'])
            ->name('receive')
            ->middleware('scope.check');
    });

    // Konfigurasi Deadline - Semua admin internal dapat mengubah konfigurasi
    Route::prefix('admin/settings')->name('admin.settings.')->group(function () {
        Route::get('/deadline', function () {
            $config = \App\Models\RevisionConfig::where('key', 'revision_deadline_days')->first();
            return view('admin.settings.deadline', compact('config'));
        })
            ->name('deadline')
            ->middleware('internal_admin');

        Route::put('/deadline', function (\Illuminate\Http\Request $request) {
            // Check permission
            if (!auth()->user()->canConfigureDeadline()) {
                abort(403, 'Anda tidak memiliki akses untuk mengubah konfigurasi deadline');
            }

            $request->validate([
                'revision_deadline_days' => 'required|integer|min:1|max:30'
            ]);

            \App\Models\RevisionConfig::updateOrCreate(
                ['key' => 'revision_deadline_days'],
                [
                    'value' => $request->revision_deadline_days,
                    'description' => 'Durasi deadline revisi dokumen (hari)',
                    'updated_by' => auth()->id(),
                ]
            );

            return back()->with('success', 'Konfigurasi deadline berhasil diupdate');
        })
            ->name('deadline.update')
            ->middleware('internal_admin');
    });

    // Download dokumen (semua admin internal bisa download)
    Route::get('/admin/documents/{requestDocument}/download', function (\App\Models\RequestDocument $requestDocument) {
        if ($requestDocument->status !== 'APPROVED') {
            abort(403, 'Hanya dokumen dengan status APPROVED yang dapat didownload');
        }
        
        if (!$requestDocument->file_path || !file_exists(storage_path('app/' . $requestDocument->file_path))) {
            abort(404, 'File dokumen tidak ditemukan');
        }

        return response()->download(
            storage_path('app/' . $requestDocument->file_path),
            $requestDocument->original_filename ?? 'document.pdf'
        );
    })
        ->name('admin.documents.download')
        ->middleware('internal_admin');

    // History dokumen (semua admin internal bisa lihat)
    Route::get('/admin/documents/{requestDocument}/history', function (\App\Models\RequestDocument $requestDocument) {
        $requestDocument->load(['histories.actor', 'documentType', 'documentRequest.supplier']);
        return view('admin.documents.history', compact('requestDocument'));
    })
        ->name('admin.documents.history')
        ->middleware('internal_admin');

    // Admin review routes for PSW, PS, and MSA forms
    Route::prefix('admin/forms')->name('admin.forms.')->middleware('internal_admin')->group(function () {
        // PSW form preview/review
        Route::get('/psw/{pswId}', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'adminPreview'])
            ->name('psw.preview');
        
        Route::get('/psw/{pswId}/pdf', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'adminViewPdf'])
            ->name('psw.pdf');
        
        // PS form preview/review
        Route::get('/packaging-standard/{psId}', [\App\Http\Controllers\PackagingStandardController::class, 'adminPreview'])
            ->name('ps.preview');
        
        Route::get('/packaging-standard/{psId}/pdf', [\App\Http\Controllers\PackagingStandardController::class, 'adminViewPdf'])
            ->name('ps.pdf');
        
        // MSA Gage R&R form preview/review
        Route::get('/msa/{msaId}', [\App\Http\Controllers\MsaGageRrController::class, 'adminPreview'])
            ->name('msa.preview');
        
        Route::get('/msa/{msaId}/pdf', [\App\Http\Controllers\MsaGageRrController::class, 'adminViewPdf'])
            ->name('msa.pdf');
    });
});

// Part Submission Warrant (PSW) Form routes (token-based, no auth required)
Route::prefix('part-submission-warrant')->name('psw.')->group(function () {
    Route::get('/form/{token}', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'showForm'])
        ->name('form');
    
    Route::get('/{pswId}/pdf', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'viewPdf'])
        ->name('pdf');
    
    Route::get('/{pswId}/download', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'downloadPdf'])
        ->name('download');
});

// Part Submission Warrant API routes (token-based, no auth required)
Route::prefix('api/part-submission-warrants')->name('api.psw.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'getForm'])
        ->name('get-form');
    
    Route::post('/{pswId}/draft', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{pswId}/submit', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'submit'])
        ->name('submit');
    
    Route::post('/{pswId}/upload-signature', [\App\Http\Controllers\PartSubmissionWarrantController::class, 'uploadSignature'])
        ->name('upload-signature');
});

// Packaging Standard Form routes (token-based, no auth required)
Route::prefix('packaging-standard')->name('ps.')->group(function () {
    Route::get('/form/{token}', [\App\Http\Controllers\PackagingStandardController::class, 'showForm'])
        ->name('form');
    
    Route::get('/{psId}/pdf', [\App\Http\Controllers\PackagingStandardController::class, 'viewPdf'])
        ->name('pdf');
    
    Route::get('/{psId}/download', [\App\Http\Controllers\PackagingStandardController::class, 'downloadPdf'])
        ->name('download');
});

// Packaging Standard API routes (token-based, no auth required)
Route::prefix('api/packaging-standards')->name('api.ps.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\PackagingStandardController::class, 'getForm'])
        ->name('get-form');
    
    Route::get('/{psId}/debug', [\App\Http\Controllers\PackagingStandardController::class, 'debugState'])
        ->name('debug-state');
    
    Route::post('/{psId}/draft', [\App\Http\Controllers\PackagingStandardController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{psId}/submit', [\App\Http\Controllers\PackagingStandardController::class, 'submit'])
        ->name('submit');
    
    Route::post('/{psId}/upload-image', [\App\Http\Controllers\PackagingStandardController::class, 'uploadImage'])
        ->name('upload-image');
    
    Route::post('/{psId}/delete-image', [\App\Http\Controllers\PackagingStandardController::class, 'deleteImage'])
        ->name('delete-image');
});

/**
 * Approval Blast Routes (Public, no authentication required)
 * Token-based access for external approvers
 */
Route::prefix('approval')
    ->name('approval.')
    ->middleware('throttle:' . config('approval_blast.throttle_requests', 20) . ',1')
    ->group(function () {
        Route::get('/{token}', [\App\Http\Controllers\ApprovalTokenController::class, 'show'])
            ->name('show');
        
        Route::post('/{token}/approve', [\App\Http\Controllers\ApprovalTokenController::class, 'approve'])
            ->name('approve');
        
        Route::post('/{token}/revise', [\App\Http\Controllers\ApprovalTokenController::class, 'revise'])
            ->name('revise');
    });
