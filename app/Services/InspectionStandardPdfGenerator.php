<?php

namespace App\Services;

use App\Models\InspectionStandard;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class InspectionStandardPdfGenerator
{
    /**
     * Generate PDF from Inspection Standard data
     */
    public function generate(InspectionStandard $inspectionStandard): string
    {
        try {
            Log::info('[PDF Generator] Starting PDF generation', [
                'inspection_standard_id' => $inspectionStandard->id
            ]);

            // Delete old PDF if exists
            if ($inspectionStandard->pdf_file_path) {
                try {
                    if (Storage::disk('documents')->exists($inspectionStandard->pdf_file_path)) {
                        Storage::disk('documents')->delete($inspectionStandard->pdf_file_path);
                        Log::info('[PDF Generator] Old PDF deleted', [
                            'old_path' => $inspectionStandard->pdf_file_path
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::warning('[PDF Generator] Failed to delete old PDF', [
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Ensure all relations are loaded
            if (!$inspectionStandard->relationLoaded('drawings')) {
                $inspectionStandard->load('drawings');
            }
            if (!$inspectionStandard->relationLoaded('inspectionItems')) {
                $inspectionStandard->load('inspectionItems');
            }
            if (!$inspectionStandard->relationLoaded('supplier')) {
                $inspectionStandard->load('supplier');
            }

            Log::info('[PDF Generator] Relations loaded', [
                'drawings_count' => $inspectionStandard->drawings->count(),
                'items_count' => $inspectionStandard->inspectionItems->count(),
                'has_supplier' => $inspectionStandard->supplier !== null
            ]);

            // Prepare data for PDF
            $data = $this->prepareData($inspectionStandard);

            Log::info('[PDF Generator] Data prepared, generating PDF...');

            // Generate PDF
            $pdf = Pdf::loadView('inspection-standard.pdf.template', $data)
                ->setPaper('a4', 'portrait')
                ->setOption('isHTML5ParserEnabled', true)
                ->setOption('isPhpEnabled', true);

            // Generate filename
            $filename = $this->generateFilename($inspectionStandard);

            // Store PDF
            $path = "inspection-standards/{$inspectionStandard->id}/{$filename}";
            
            Log::info('[PDF Generator] Storing PDF', ['path' => $path]);
            
            Storage::disk('documents')->put($path, $pdf->output());

            // Update inspection standard with PDF path
            $inspectionStandard->update([
                'pdf_file_path' => $path,
            ]);

            Log::info("[PDF Generator] PDF generated successfully", [
                'path' => $path,
                'filename' => $filename,
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error("[PDF Generator] Error generating PDF", [
                'inspection_standard_id' => $inspectionStandard->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Prepare data for PDF template
     */
    private function prepareData(InspectionStandard $inspectionStandard): array
    {
        try {
            // Ensure all relations are loaded
            if (!$inspectionStandard->relationLoaded('drawings')) {
                $inspectionStandard->load('drawings');
            }
            if (!$inspectionStandard->relationLoaded('inspectionItems')) {
                $inspectionStandard->load('inspectionItems');
            }
            if (!$inspectionStandard->relationLoaded('supplier')) {
                $inspectionStandard->load('supplier');
            }
            if (!$inspectionStandard->relationLoaded('requestDocument')) {
                $inspectionStandard->load('requestDocument');
            }

            Log::info('[PDF Generator] Preparing data...');

            // Generate QR codes for digital signatures
            $qrCodes = $this->generateSignatureQRCodes($inspectionStandard);

            $data = [
                'inspectionStandard' => $inspectionStandard,
                'generalInformation' => [
                    'model' => $inspectionStandard->model ?? '',
                    'dateIssued' => $inspectionStandard->date_issued?->format('d M Y') ?? '',
                    'refNo' => $inspectionStandard->ref_no ?? '',
                    'partRoute' => $inspectionStandard->part_route ?? '',
                    'partNo' => $inspectionStandard->part_no ?? '',
                    'partName' => $inspectionStandard->part_name ?? '',
                    'manufacturer' => $inspectionStandard->manufacturer ?? '',
                    'material' => $inspectionStandard->material ?? '',
                    'generalTolerance' => $inspectionStandard->general_tolerance ?? '',
                    'weight' => $inspectionStandard->weight ?? '',
                    'revisionDate' => $inspectionStandard->revision_date?->format('d M Y') ?? '',
                    'revisionRecord' => $inspectionStandard->revision_record ?? '',
                ],
                'supplierInfo' => [
                    'supplier' => $inspectionStandard->supplier?->name ?? '',
                    'customer' => 'AAIJ (Purchasing & Quality Division)',
                    'manufacturer' => $inspectionStandard->manufacturer ?? '',
                ],
                'drawings' => $inspectionStandard->drawings
                    ->sortBy('sequence_number')
                    ->map(function($d) {
                        $filePath = null;
                        try {
                            if (!empty($d->file_path) && Storage::disk('documents')->exists($d->file_path)) {
                                $fullPath = Storage::disk('documents')->path($d->file_path);
                                // Verify it's a file, not a directory
                                if (is_file($fullPath)) {
                                    $filePath = $fullPath;
                                } else {
                                    Log::warning('[PDF Generator] Drawing path is not a file', [
                                        'drawing_id' => $d->id,
                                        'path' => $d->file_path,
                                        'full_path' => $fullPath
                                    ]);
                                }
                            }
                        } catch (\Exception $e) {
                            Log::warning('[PDF Generator] Error getting drawing file path', [
                                'drawing_id' => $d->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        // Extract revision to variable to avoid parse error
                        $rev = $d->revision ?? '';

                        return [
                            'drawingName' => $d->drawing_name ?? '',
                            'drawingNo' => $d->drawing_no ?? '',
                            'revision' => $rev,
                            'description' => $d->description ?? '',
                            'filePath' => $filePath,
                            'fileType' => $d->file_type ?? '',
                            'originalFilename' => $d->original_filename ?? '',
                        ];
                    })
                    ->toArray(),
                'inspectionItems' => $inspectionStandard->inspectionItems
                    ->sortBy('sequence_number')
                    ->map(function($i, $index) {
                        return [
                            'no' => $index + 1,
                            'inspectionItem' => $i->inspection_item ?? '',
                            'inspectionInstrument' => $i->inspection_instrument ?? '',
                            'inspectionCriterion' => $i->inspection_criterion ?? '',
                            'rank' => $i->rank ?? '',
                            'supplierSampling' => $i->supplier_sampling ?? '',
                            'aaijSampling' => $i->aaij_sampling ?? '',
                            'remarkInspectionMethod' => $i->remark_inspection_method ?? '',
                        ];
                    })
                    ->values() // Reset array keys
                    ->toArray(),
                'revisions' => [], // Add empty revisions array for now
                'qrCodes' => $qrCodes, // Add QR codes to data
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'pageNumber' => 1,
            ];

            Log::info('[PDF Generator] Data prepared successfully', [
                'drawings_count' => count($data['drawings']),
                'items_count' => count($data['inspectionItems']),
                'qr_codes' => array_keys($qrCodes),
            ]);

            return $data;
        } catch (\Exception $e) {
            Log::error('[PDF Generator] Error preparing data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Generate QR codes for digital signatures
     *
     * @param InspectionStandard $inspectionStandard
     * @return array
     */
    private function generateSignatureQRCodes(InspectionStandard $inspectionStandard): array
    {
        $qrCodes = [];
        $qrService = app(\App\Services\QRCodeService::class);
        
        try {
            $requestDocument = $inspectionStandard->requestDocument;
            
            if (!$requestDocument) {
                return $qrCodes;
            }

            // Receiver QR Code (for IS only)
            if ($requestDocument->receiver_esign_token) {
                $signature = \App\Models\CheckerSignature::where('token', $requestDocument->receiver_esign_token)->first();
                if ($signature) {
                    $qrCodes['receiver'] = $qrService->generateForSignature($signature);
                    Log::info('[PDF Generator] Receiver QR code generated');
                }
            }

            // Checker QR Code
            if ($requestDocument->checker_esign_token) {
                $signature = \App\Models\CheckerSignature::where('token', $requestDocument->checker_esign_token)->first();
                if ($signature) {
                    $qrCodes['checker'] = $qrService->generateForSignature($signature);
                    Log::info('[PDF Generator] Checker QR code generated');
                }
            }

            // Approver QR Codes (from document_approvals)
            $approvals = $requestDocument->approvals()->where('status', 'APPROVED')->get();
            foreach ($approvals as $approval) {
                if ($approval->signature) {
                    $scope = strtolower($approval->scope);
                    $qrCodes["approver_{$scope}"] = $qrService->generateForSignature($approval->signature);
                    Log::info('[PDF Generator] Approver QR code generated', ['scope' => $scope]);
                }
            }

        } catch (\Exception $e) {
            Log::error('[PDF Generator] Error generating QR codes', [
                'error' => $e->getMessage()
            ]);
            // Continue without QR codes rather than failing the entire PDF generation
        }

        return $qrCodes;
    }

    /**
     * Generate filename for PDF
     */
    private function generateFilename(InspectionStandard $inspectionStandard): string
    {
        $refNo = $inspectionStandard->ref_no ? \Illuminate\Support\Str::slug($inspectionStandard->ref_no) : 'IS';
        $partNo = $inspectionStandard->part_no ? \Illuminate\Support\Str::slug($inspectionStandard->part_no) : 'PART';
        $timestamp = Carbon::now()->format('YmdHis');

        return "INSPECTION_STANDARD_{$refNo}_{$partNo}_{$timestamp}.pdf";
    }

    /**
     * Download PDF file
     */
    public function download(InspectionStandard $inspectionStandard)
    {
        if (!$inspectionStandard->pdf_file_path) {
            throw new \Exception('PDF not generated for this Inspection Standard');
        }

        $filePath = Storage::disk('documents')->path($inspectionStandard->pdf_file_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception('PDF file not found');
        }

        $filename = basename($filePath);
        return response()->download($filePath, $filename);
    }

    /**
     * View PDF file
     */
    public function view(InspectionStandard $inspectionStandard)
    {
        if (!$inspectionStandard->pdf_file_path) {
            throw new \Exception('PDF not generated for this Inspection Standard');
        }

        $filePath = Storage::disk('documents')->path($inspectionStandard->pdf_file_path);
        
        if (!file_exists($filePath)) {
            throw new \Exception('PDF file not found');
        }

        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . basename($filePath) . '"',
        ]);
    }
}
