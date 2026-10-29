<?php

namespace App\Http\Controllers;

use App\Events\DocumentSubmitted;
use App\Models\MsaGageRr;
use App\Services\GageRRCalculator;
use App\Services\MsaValidationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MsaGageRrController extends Controller
{
    /** Halaman form: GET /supplier/msa/{token} */
    public function page(string $token)
    {
        $msa = MsaGageRr::with(['requestDocument', 'supplier'])
            ->where('token', $token)
            ->firstOrFail();
        
        // Validasi relasi
        if (!$msa->requestDocument || !$msa->supplier) {
            abort(403, 'Invalid document association.');
        }
        
        return view('supplier.msa-gage-rr');
    }

    /** Data awal form: GET /api/msa/form/{token} */
    public function form(string $token): JsonResponse
    {
        $r = MsaGageRr::with(['requestDocument.requestPart', 'supplier'])
            ->where('token', $token)
            ->firstOrFail();

        // Validasi relasi
        if (!$r->requestDocument || !$r->supplier) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid document association.',
            ], 403);
        }

        $general = $r->general ?? [];
        
        // Get part info from requestDocument -> requestPart
        $partNo = '';
        $partName = '';
        
        if ($r->requestDocument && $r->requestDocument->requestPart) {
            $partNo = $r->requestDocument->requestPart->part_number ?? '';
            $partName = $r->requestDocument->requestPart->part_name ?? '';
        }
        
        $general['partNoName'] = trim($partNo . ($partName ? ' / ' . $partName : ''));

        return response()->json([
            'id'                 => $r->id,
            'status'             => $r->status,
            'generalInformation' => $general,
            'settings'           => $r->settings ?? ['appraisers' => 3, 'trials' => 3, 'parts' => 10],
            'measurements'       => $r->measurements ?? new \stdClass(),
        ]);
    }

    /** POST /api/msa/{id}/draft */
    public function saveDraft(Request $request, int $id): JsonResponse
    {
        $r = $this->findEditable($id);
        $data = $this->validated($request, false);
        
        // Change status from PENDING to DRAFT when user saves for the first time
        $extra = [];
        if ($r->status === 'PENDING' || $r->status === null) {
            $extra['status'] = 'DRAFT';
        }
        
        $this->persist($r, $data, $extra);
        return response()->json(['success' => true]);
    }

    /** POST /api/msa/{id}/submit */
    public function submit(Request $request, int $id): JsonResponse
    {
        $r = $this->findEditable($id);
        $data = $this->validated($request, true);

        // DEBUG: Log the measurements structure
        \Log::info('MSA Submit - Measurements structure:', [
            'measurements_keys' => array_keys($data['measurements'] ?? []),
            'settings' => $data['settings'] ?? [],
            'sample_data' => isset($data['measurements']['A']) ? [
                'A_structure' => [
                    'type' => gettype($data['measurements']['A']),
                    'count' => count($data['measurements']['A']),
                    'first_trial' => $data['measurements']['A'][0] ?? 'not set',
                ]
            ] : 'A not set'
        ]);

        // Validasi lengkap sebelum submit
        $validation = MsaValidationService::validateBeforeSubmission($data);
        
        if (!$validation['is_valid']) {
            \Log::warning('MSA Submit - Validation failed:', $validation);
            
            return response()->json([
                'success' => false,
                'message' => 'Form validation failed',
                'errors' => $validation['errors'],
                'debug' => [
                    'measurements_keys' => array_keys($data['measurements'] ?? []),
                    'settings' => $data['settings'] ?? [],
                ]
            ], 422);
        }

        // Compute hasil GageRR
        $calc = GageRRCalculator::compute(
            $this->withPart($r, $data['generalInformation']), 
            $data['settings'], 
            $data['measurements']
        );
        
        if (!$calc['complete']) {
            return response()->json([
                'success' => false,
                'message' => 'Data pengukuran belum lengkap.',
            ], 422);
        }

        // Persist data with SUBMITTED status (uppercase to match IS convention)
        $this->persist($r, $data, [
            'status'       => 'SUBMITTED',
            'submitted_at' => now(),
            'results'      => $calc['result'],
        ]);

        // Update request document status
        $requestDocument = $r->requestDocument;
        if ($requestDocument) {
            // Check if this is a revision resubmission
            $isRevision = $requestDocument->status === 'REVISE';
            
            if ($isRevision) {
                // Use the resubmit method to handle revision logic
                // For MSA, we pass 'form' as the path since it's a form-based document
                $requestDocument->resubmit('form');
            } else {
                // Normal first-time submission
                $requestDocument->update([
                    'status' => 'SUBMITTED',
                    'submitted_at' => now(),
                    'file_path' => 'form',
                    'pdf_path' => 'form', // Consistency with other documents
                ]);
            }
            
            $requestDocument->refresh(); // Force reload to ensure data is fresh
            
            // Dispatch DocumentSubmitted event to trigger approval blast email
            DocumentSubmitted::dispatch($requestDocument, 'MSA');
        }

        return response()->json(['success' => true, 'redirect' => '/supplier/dashboard']);
    }

    /** PDF (stream di browser): GET /api/msa/{id}/pdf   (?plain=1 → tanpa warna hijau pada sel input) */
    public function pdf(Request $request, int $id)
    {
        $r = MsaGageRr::with(['requestDocument', 'supplier'])->findOrFail($id);
        
        // Validasi relasi
        if (!$r->requestDocument || !$r->supplier) {
            abort(403, 'Invalid document association.');
        }

        $calc = GageRRCalculator::compute(
            $this->withPart($r, $r->general ?? []),
            $r->settings ?? [],
            $r->measurements ?? []
        );

        $pdf = Pdf::loadView('pdf.msa-gage-rr', [
                'v'    => $calc['v'],
                'fill' => $request->boolean('plain') ? '#ffffff' : '#ccffcc',
            ])
            ->setPaper('letter', 'portrait')
            ->setOption(['dpi' => 72, 'defaultFont' => 'Helvetica', 'isRemoteEnabled' => false]);

        $name = 'MSA-GageRR-' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $r->part_no ?: $r->id) . '.pdf';
        return $pdf->stream($name);
    }

    /** PDF Download: GET /api/msa/{id}/download */
    public function download(Request $request, int $id)
    {
        $r = MsaGageRr::with(['requestDocument', 'supplier'])->findOrFail($id);
        
        // Validasi relasi
        if (!$r->requestDocument || !$r->supplier) {
            abort(403, 'Invalid document association.');
        }

        $calc = GageRRCalculator::compute(
            $this->withPart($r, $r->general ?? []),
            $r->settings ?? [],
            $r->measurements ?? []
        );

        $pdf = Pdf::loadView('pdf.msa-gage-rr', [
                'v'    => $calc['v'],
                'fill' => $request->boolean('plain') ? '#ffffff' : '#ccffcc',
            ])
            ->setPaper('letter', 'portrait')
            ->setOption(['dpi' => 72, 'defaultFont' => 'Helvetica', 'isRemoteEnabled' => false]);

        $name = 'MSA-GageRR-' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $r->part_no ?: $r->id) . '.pdf';
        return $pdf->download($name);
    }

    /** Admin preview: GET /admin/forms/msa/{msaId} (untuk review di admin) */
    public function adminPreview(int $msaId)
    {
        $msaGageRr = MsaGageRr::with(['requestDocument', 'supplier'])->findOrFail($msaId);
        
        if (!$msaGageRr->requestDocument || !$msaGageRr->supplier) {
            abort(403, 'Invalid document association.');
        }
        
        return view('admin.forms.msa-preview', compact('msaGageRr'));
    }

    /** Admin PDF view: GET /admin/forms/msa/{msaId}/pdf (untuk download/view di admin) */
    public function adminViewPdf(Request $request, int $msaId)
    {
        return $this->pdf($request, $msaId);
    }

    // ------------------------------------------------------------------

    private function findEditable(int $id): MsaGageRr
    {
        $r = MsaGageRr::with(['requestDocument', 'supplier'])->findOrFail($id);
        
        // Validasi: pastikan record memiliki relasi yang valid
        if (!$r->requestDocument || !$r->supplier) {
            abort(403, 'Invalid document association.');
        }
        
        // Validasi: pastikan dokumen belum disubmit
        abort_if($r->status === 'submitted', 423, 'Dokumen sudah disubmit.');
        
        return $r;
    }

    private function validated(Request $request, bool $strict): array
    {
        $req = $strict ? 'required' : 'nullable';
        
        // Get settings to check number of appraisers
        $settings = $request->input('settings', []);
        $numAppraisers = $settings['appraisers'] ?? 3;
        
        // AppraiserC is required only if there are 3 appraisers
        $appraiserCRule = ($numAppraisers === 3) ? "$req|string|max:100" : 'nullable|string|max:100';

        // Simplified validation - accept both string and numeric keys
        $validated = $request->validate([
            'generalInformation'                  => 'required|array',
            'generalInformation.characteristics'  => "$req|string|max:100",
            'generalInformation.specifications'   => "$req|string|max:100",
            'generalInformation.gageName'         => "$req|string|max:100",
            'generalInformation.gageNo'           => 'nullable|string|max:100',
            'generalInformation.gageType'         => "$req|string|max:100",
            'generalInformation.date'             => "$req|date",
            'generalInformation.performedBy'      => "$req|string|max:100",
            'generalInformation.appraiserA'       => "$req|string|max:100",
            'generalInformation.appraiserB'       => "$req|string|max:100",
            'generalInformation.appraiserC'       => $appraiserCRule,
            'settings'                            => 'required|array',
            'settings.appraisers'                 => 'required|integer|in:2,3',
            'settings.trials'                     => 'required|integer|in:2,3',
            'settings.parts'                      => 'required|integer|between:2,10',
            'measurements'                        => 'required|array',
        ]);

        // Validate measurements structure manually to support both formats
        if (isset($validated['measurements'])) {
            foreach ($validated['measurements'] as $appKey => $trials) {
                if (!is_array($trials)) {
                    throw new \InvalidArgumentException("Measurements[$appKey] must be an array");
                }
                foreach ($trials as $trialKey => $parts) {
                    if (!is_array($parts)) {
                        throw new \InvalidArgumentException("Measurements[$appKey][$trialKey] must be an array");
                    }
                    foreach ($parts as $partKey => $value) {
                        if ($value !== null && !is_numeric($value)) {
                            throw new \InvalidArgumentException("Measurements[$appKey][$trialKey][$partKey] must be numeric or null");
                        }
                    }
                }
            }
        }

        return $validated;
    }

    private function persist(MsaGageRr $r, array $data, array $extra = []): void
    {
        $general = collect($data['generalInformation'])->except('partNoName')->all();
        $r->fill(array_merge([
            'general'      => $general,
            'settings'     => $data['settings'],
            'measurements' => $data['measurements'],
        ], $extra))->save();
    }

    /** Kolom "Part No. & Name" selalu diambil dari record (bukan dari input user). */
    private function withPart(MsaGageRr $r, array $general): array
    {
        $general['partNoName'] = trim(($r->part_no ?? '') . ($r->part_name ? ' / ' . $r->part_name : ''));
        return $general;
    }
}
