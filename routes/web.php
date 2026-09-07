<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SignedUrlController;

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

// Portal access via token (no login required)
Route::get('/portal/{token}', [\App\Http\Controllers\PortalController::class, 'accessViaToken'])
    ->name('portal.access');

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
Route::prefix('api/inspection-standards')->name('api.inspection-standards.')->group(function () {
    Route::get('/{token}', [\App\Http\Controllers\InspectionStandardController::class, 'getForm'])
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

// Admin Panel Routes (Authentication required)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard')
        ->middleware('internal_admin');

    Route::get('/dashboard/not-submitted', [DashboardController::class, 'notSubmitted'])
        ->name('dashboard.not_submitted')
        ->middleware('internal_admin');

    Route::get('/dashboard/in-progress', [DashboardController::class, 'inProgress'])
        ->name('dashboard.in_progress')
        ->middleware('internal_admin');

    Route::get('/dashboard/completed', [DashboardController::class, 'completed'])
        ->name('dashboard.completed')
        ->middleware('internal_admin');

    // Admin Request Management - Semua admin internal bisa akses, tapi hanya QA yang bisa create
    Route::prefix('requests')->name('requests.')->group(function () {
        Route::get('/', [RequestController::class, 'index'])
            ->name('index')
            ->middleware('internal_admin');

        Route::get('/create', [RequestController::class, 'create'])
            ->name('create')
            ->middleware('internal_admin'); // Controller akan check canCreateRequest()

        Route::post('/', [RequestController::class, 'store'])
            ->name('store')
            ->middleware('internal_admin'); // Controller akan check canCreateRequest()

        Route::get('/{documentRequest}', [RequestController::class, 'show'])
            ->name('show')
            ->middleware('internal_admin');

        Route::get('/{documentRequest}/review', [RequestController::class, 'review'])
            ->name('review')
            ->middleware('internal_admin');

        Route::post('/{documentRequest}/resend-email', [RequestController::class, 'resendEmail'])
            ->name('resend-email')
            ->middleware('internal_admin');
    });

    // Admin Review & Approval (dengan scope checking sesuai Implementation Document)
    Route::prefix('review')->name('review.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])
            ->name('index')
            ->middleware('internal_admin');

        Route::get('/{requestDocument}', [ReviewController::class, 'show'])
            ->name('show')
            ->middleware(['internal_admin', 'scope.check']);

        Route::post('/{requestDocument}/approve', [ReviewController::class, 'approve'])
            ->name('approve')
            ->middleware(['internal_admin', 'scope.check']);

        Route::post('/{requestDocument}/reject', [ReviewController::class, 'reject'])
            ->name('reject')
            ->middleware(['internal_admin', 'scope.check']);
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
    
    Route::post('/{psId}/draft', [\App\Http\Controllers\PackagingStandardController::class, 'saveDraft'])
        ->name('save-draft');
    
    Route::post('/{psId}/submit', [\App\Http\Controllers\PackagingStandardController::class, 'submit'])
        ->name('submit');
    
    Route::post('/{psId}/upload-image', [\App\Http\Controllers\PackagingStandardController::class, 'uploadImage'])
        ->name('upload-image');
    
    Route::post('/{psId}/delete-image', [\App\Http\Controllers\PackagingStandardController::class, 'deleteImage'])
        ->name('delete-image');
});
