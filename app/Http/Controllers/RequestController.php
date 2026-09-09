<?php

namespace App\Http\Controllers;

use App\Models\DocumentRequest;
use App\Models\Supplier;
use App\Services\NotificationService;
use App\Services\RequestService;
use App\Services\ReviewApprovalService;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    private RequestService $requestService;
    private NotificationService $notificationService;
    private ReviewApprovalService $reviewService;

    public function __construct(
        RequestService $requestService,
        NotificationService $notificationService,
        ReviewApprovalService $reviewService
    ) {
        $this->middleware('auth');
        $this->middleware('internal_admin');
        $this->requestService = $requestService;
        $this->notificationService = $notificationService;
        $this->reviewService = $reviewService;
    }

    /**
     * Tampilkan daftar document requests
     */
    public function index(Request $request)
    {
        $requests = $this->requestService->getAllRequests(
            $request->only(['supplier_id', 'status', 'date_from', 'date_to'])
        );

        $suppliers = Supplier::all();

        return view('admin.requests.index', compact('requests', 'suppliers'));
    }

    /**
     * Tampilkan form untuk membuat request baru
     */
    public function create()
    {
        // Hanya Admin QA yang dapat membuat request
        if (!auth()->user()->canCreateRequest()) {
            abort(403, 'Hanya Admin QA yang dapat membuat document request');
        }

        $suppliers = Supplier::all();
        $documentTypes = \App\Models\DocumentType::all();

        return view('admin.requests.create', compact('suppliers', 'documentTypes'));
    }

    /**
     * Simpan document request baru
     */
    public function store(Request $request)
    {
        // Hanya Admin QA yang dapat membuat request
        if (!auth()->user()->canCreateRequest()) {
            abort(403, 'Hanya Admin QA yang dapat membuat document request');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'model' => 'required|string|max:100',
            'due_date' => 'required|date|after:today',
            'parts' => 'required|array|min:1',
            'parts.*.part_name' => 'required|string|max:255',
            'parts.*.part_number' => 'required|string|max:100',
            'selected_documents' => 'required|array|min:1',
            'selected_documents.*' => 'required|exists:document_types,id',
        ], [
            'selected_documents.required' => 'Please select at least one document requirement.',
            'selected_documents.min' => 'Please select at least one document requirement.',
        ]);

        try {
            $documentRequest = $this->requestService->createRequestWithParts(
                $validated,
                auth()->id()
            );

            // Send request letter email - pass only the model without deep relations
            $this->notificationService->sendRequestLetter($documentRequest->withoutRelations());

            return redirect()
                ->route('requests.show', $documentRequest)
                ->with('success', 'Document request berhasil dibuat dan email surat request telah dikirim');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Gagal membuat request: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail document request
     */
    public function show(DocumentRequest $documentRequest)
    {
        $request = $this->requestService->getById($documentRequest->id);

        return view('admin.requests.show', compact('request'));
    }

    /**
     * Tampilkan halaman review dokumen untuk Admin QA
     */
    public function review(DocumentRequest $documentRequest)
    {
        // Hanya Admin QA yang dapat mengakses halaman review
        if (!auth()->user()->isAdminQA()) {
            abort(403, 'Hanya Admin QA yang dapat mengakses halaman review dokumen');
        }

        $request = $this->requestService->getById($documentRequest->id);
        
        // Filter dokumen yang sudah disubmit dan dapat direview oleh QA
        $submittedDocuments = $request->requestDocuments
            ->where('status', 'SUBMITTED')
            ->filter(function($document) {
                $scopes = $document->documentType->scope ?? [];
                return in_array('QA', $scopes);
            });
        
        if ($submittedDocuments->isEmpty()) {
            return redirect()
                ->route('requests.show', $documentRequest)
                ->with('info', 'Tidak ada dokumen yang perlu direview oleh QA saat ini');
        }

        return view('admin.requests.review', compact('request', 'submittedDocuments'));
    }

    /**
     * Resend request letter email
     */
    public function resendEmail(DocumentRequest $documentRequest)
    {
        // Check permission untuk trigger ulang notifikasi
        if (!auth()->user()->canTriggerNotification()) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses untuk mengirim ulang email notifikasi'
                ], 403);
            }
            abort(403, 'Anda tidak memiliki akses untuk mengirim ulang email notifikasi');
        }

        try {
            $this->requestService->resendRequestLetter($documentRequest->id);

            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Surat request berhasil dikirim ulang ke supplier'
                ]);
            }

            return back()->with('success', 'Surat request berhasil dikirim ulang ke supplier');
        } catch (\Exception $e) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirim ulang surat request: ' . $e->getMessage()
                ], 500);
            }

            return back()->with('error', 'Gagal mengirim ulang surat request: ' . $e->getMessage());
        }
    }
}
