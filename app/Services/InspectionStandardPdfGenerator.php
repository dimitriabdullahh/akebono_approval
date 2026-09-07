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

            Log::info('[PDF Generator] Preparing data...');

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
                            if ($d->file_path && Storage::disk('documents')->exists($d->file_path)) {
                                $filePath = Storage::disk('documents')->path($d->file_path);
                            }
                        } catch (\Exception $e) {
                            Log::warning('[PDF Generator] Error getting drawing file path', [
                                'drawing_id' => $d->id,
                                'error' => $e->getMessage()
                            ]);
                        }

                        return [
                            'drawingName' => $d->drawing_name ?? '',
                            'drawingNo' => $d->drawing_no ?? '',
                            'revision' => $d->revision ?? '',
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
                'generatedAt' => Carbon::now()->format('d M Y H:i:s'),
                'pageNumber' => 1,
            ];

            Log::info('[PDF Generator] Data prepared successfully', [
                'drawings_count' => count($data['drawings']),
                'items_count' => count($data['inspectionItems'])
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
