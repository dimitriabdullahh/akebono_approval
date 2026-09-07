<?php

namespace App\Services;

use App\Models\Supplier;
use App\Models\DocumentRequest;
use Illuminate\Support\Collection;

class OutstandingDocumentService
{
    /**
     * Get outstanding documents for a specific supplier
     * 
     * @param int $supplierId
     * @param string $sortBy (due_date, request_date, status)
     * @param string $sortDirection (asc, desc)
     * @return Collection
     */
    public function getOutstandingForSupplier(
        int $supplierId,
        string $sortBy = 'due_date',
        string $sortDirection = 'asc'
    ): Collection {
        $supplier = Supplier::findOrFail($supplierId);
        
        // Get all requests for this supplier
        $requests = DocumentRequest::where('supplier_id', $supplierId)
            ->with(['requestDocuments.documentType', 'requestParts', 'supplier'])
            ->where(function ($query) {
                // Exclude yang sudah dismissed alert (dismissed kurang dari 7 hari yang lalu)
                $query->whereNull('alert_dismissed_at')
                    ->orWhere('alert_dismissed_at', '<=', now()->subDays(7));
            })
            ->get();

        // Filter only outstanding requests
        $requests = $requests->filter(function ($request) {
            return $request->isOutstanding();
        });

        // Format data untuk alert box
        $formatted = $requests->map(function ($request) {
            return [
                'id' => $request->id,
                'request_date' => $request->created_at->format('Y-m-d'),
                'request_date_formatted' => $request->created_at->format('d M Y'),
                'model' => $request->model,
                'part_number' => $request->part_number,
                'due_date' => $request->due_date->format('Y-m-d'),
                'due_date_formatted' => $request->due_date->format('d M Y'),
                'status' => $request->outstanding_status, // 'open' atau 'reject'
                'status_label' => $this->getStatusLabel($request->outstanding_status),
                'status_class' => $this->getStatusClass($request->outstanding_status),
                'portal_url' => $request->portal_upload_url,
                'days_remaining' => $request->due_date->diffInDays(now(), false),
                'is_overdue' => $request->due_date->copy()->startOfDay()->lt(now()->startOfDay()),
                'total_documents' => $request->requestDocuments->count(),
                'submitted_documents' => $request->requestDocuments->whereIn('status', ['SUBMITTED', 'APPROVED', 'REVISE'])->count(),
                'revision_documents' => $request->requestDocuments->where('status', 'REVISE')->count(),
            ];
        });

        // Apply sorting
        $formatted = $this->applySorting($formatted, $sortBy, $sortDirection);

        return $formatted;
    }

    /**
     * Get outstanding documents with supplier grouping
     * Untuk internal admin view
     */
    public function getAllOutstandingGroupedBySupplier(): Collection
    {
        $suppliers = Supplier::with(['documentRequests' => function ($query) {
            $query->with(['requestDocuments.documentType', 'requestParts']);
        }])->get();

        $grouped = $suppliers->map(function ($supplier) {
            $outstanding = $supplier->documentRequests->filter(function ($request) {
                return $request->isOutstanding();
            });

            if ($outstanding->isEmpty()) {
                return null;
            }

            return [
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'vendor_code' => $supplier->vendor_code,
                'pic_name' => $supplier->pic_name,
                'pic_email' => $supplier->pic_email,
                'outstanding_count' => $outstanding->count(),
                'requests' => $outstanding->map(function ($request) {
                    return [
                        'id' => $request->id,
                        'request_date' => $request->created_at->format('Y-m-d'),
                        'request_date_formatted' => $request->created_at->format('d M Y'),
                        'model' => $request->model,
                        'due_date' => $request->due_date->format('Y-m-d'),
                        'due_date_formatted' => $request->due_date->format('d M Y'),
                        'status' => $request->outstanding_status,
                        'status_label' => $this->getStatusLabel($request->outstanding_status),
                        'is_overdue' => $request->due_date->isPast(),
                    ];
                })->values(),
            ];
        })->filter()->values();

        return $grouped;
    }

    /**
     * Dismiss alert for specific request
     */
    public function dismissAlert(int $requestId): bool
    {
        $request = DocumentRequest::findOrFail($requestId);
        $request->dismissAlert();
        return true;
    }

    /**
     * Dismiss all alerts for a supplier
     */
    public function dismissAllAlertsForSupplier(int $supplierId): int
    {
        $updated = DocumentRequest::where('supplier_id', $supplierId)
            ->whereNull('alert_dismissed_at')
            ->update(['alert_dismissed_at' => now()]);

        return $updated;
    }

    /**
     * Get statistics for outstanding documents
     */
    public function getStatistics(int $supplierId = null): array
    {
        $query = DocumentRequest::query();
        
        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $requests = $query->with(['requestDocuments'])->get()
            ->filter(function ($request) {
                return $request->isOutstanding();
            });

        $openCount = $requests->filter(function ($request) {
            return $request->outstanding_status === 'open';
        })->count();

        $rejectCount = $requests->filter(function ($request) {
            return $request->outstanding_status === 'reject';
        })->count();

        $overdueCount = $requests->filter(function ($request) {
            return $request->due_date->isPast();
        })->count();

        return [
            'total' => $requests->count(),
            'open' => $openCount,
            'reject' => $rejectCount,
            'overdue' => $overdueCount,
            'on_track' => $requests->count() - $overdueCount,
        ];
    }

    /**
     * Apply sorting to collection
     */
    private function applySorting(Collection $collection, string $sortBy, string $sortDirection): Collection
    {
        $ascending = strtolower($sortDirection) === 'asc';

        switch ($sortBy) {
            case 'request_date':
                return $ascending 
                    ? $collection->sortBy('request_date')->values()
                    : $collection->sortByDesc('request_date')->values();
            
            case 'due_date':
                return $ascending 
                    ? $collection->sortBy('due_date')->values()
                    : $collection->sortByDesc('due_date')->values();
            
            case 'status':
                return $ascending 
                    ? $collection->sortBy('status')->values()
                    : $collection->sortByDesc('status')->values();
            
            case 'model':
                return $ascending 
                    ? $collection->sortBy('model')->values()
                    : $collection->sortByDesc('model')->values();
            
            default:
                return $collection->sortBy('due_date')->values();
        }
    }

    /**
     * Get status label for display
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'open' => 'Open',
            'reject' => 'Reject',
            default => 'Unknown',
        };
    }

    /**
     * Get CSS class for status badge
     */
    private function getStatusClass(string $status): string
    {
        return match($status) {
            'open' => 'bg-yellow-100 text-yellow-800',
            'reject' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
