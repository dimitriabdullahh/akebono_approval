<?php

namespace App\Services;

use App\Models\DocumentRequest;
use App\Models\RequestDocument;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get dashboard metrics untuk Purchasing
     */
    public function getDashboardMetrics()
    {
        $totalRequests = DocumentRequest::count();
        $pendingReview = RequestDocument::where('status', 'SUBMITTED')->count();
        $inRevision = RequestDocument::where('status', 'REVISE')->count();
        $completedProjects = $this->getCompletedProjects()->count();

        return [
            'total_requests' => $totalRequests,
            'pending_review' => $pendingReview,
            'in_revision' => $inRevision,
            'completed_projects' => $completedProjects,
        ];
    }

    /**
     * Klasifikasi 3 kategori status proyek
     */
    public function classifyProjects()
    {
        $notSubmitted = $this->getNotSubmittedProjects();
        $inProgress = $this->getInProgressProjects();
        $completed = $this->getCompletedProjects();

        return [
            'not_submitted' => [
                'count' => $notSubmitted->count(),
                'label' => '🔴 Belum Kirim Dokumen',
                'projects' => $notSubmitted,
            ],
            'in_progress' => [
                'count' => $inProgress->count(),
                'label' => '🟡 Sedang Berjalan / Belum Lengkap',
                'projects' => $inProgress,
            ],
            'completed' => [
                'count' => $completed->count(),
                'label' => '🟢 Lengkap / Seluruhnya Approved',
                'projects' => $completed,
            ],
        ];
    }

    /**
     * Get projects yang belum ada dokumen submitted
     */
    private function getNotSubmittedProjects(): Collection
    {
        return DocumentRequest::with(['supplier', 'requestDocuments.documentType'])
            ->get()
            ->filter(function ($request) {
                $submitted = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                    ->count();

                return $submitted === 0;
            });
    }

    /**
     * Get projects yang sedang berjalan / belum lengkap
     */
    private function getInProgressProjects(): Collection
    {
        return DocumentRequest::with(['supplier', 'requestDocuments.documentType'])
            ->get()
            ->filter(function ($request) {
                $mandatory = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->count();

                $approved = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->where('status', 'APPROVED')
                    ->count();

                $submitted = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])
                    ->count();

                // Jika ada dokumen submitted dan belum semua approved
                return $submitted > 0 && $approved < $mandatory;
            });
    }

    /**
     * Get projects yang sudah lengkap (100% mandatory approved)
     */
    private function getCompletedProjects(): Collection
    {
        return DocumentRequest::with(['supplier', 'requestDocuments.documentType'])
            ->get()
            ->filter(function ($request) {
                $mandatory = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->count();

                $approved = $request->requestDocuments()
                    ->whereHas('documentType', function ($q) {
                        $q->where('is_mandatory', true);
                    })
                    ->where('status', 'APPROVED')
                    ->count();

                return $approved === $mandatory && $mandatory > 0;
            });
    }

    /**
     * Get approval progress summary
     */
    public function getApprovalProgressSummary()
    {
        $requests = DocumentRequest::with(['supplier', 'requestDocuments.documentType'])->get();

        return $requests->map(function ($request) {
            $mandatory = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->count();

            $approved = $request->requestDocuments()
                ->whereHas('documentType', function ($q) {
                    $q->where('is_mandatory', true);
                })
                ->where('status', 'APPROVED')
                ->count();

            $percentage = $mandatory > 0 ? (int) (($approved / $mandatory) * 100) : 0;

            return [
                'id' => $request->id,
                'supplier' => $request->supplier->name,
                'pic_name' => $request->supplier->pic_name,
                'model' => $request->model,
                'part_number' => $request->part_number,
                'part_name' => $request->part_name,
                'total_mandatory' => $mandatory,
                'approved' => $approved,
                'percentage' => $percentage,
                'category' => $request->project_category,
            ];
        });
    }

    /**
     * Get documents yang sedang dalam review
     */
    public function getDocumentsInReview()
    {
        return RequestDocument::where('status', 'SUBMITTED')
            ->with(['documentRequest.supplier', 'documentType'])
            ->orderByDesc('created_at')
            ->paginate(10);
    }

    /**
     * Get documents dalam status revise dengan deadline info
     */
    public function getRevisionDocumentsWithDeadline()
    {
        return RequestDocument::where('status', 'REVISE')
            ->with(['documentRequest.supplier', 'documentType'])
            ->orderBy('revision_deadline_at')
            ->paginate(10);
    }
}
