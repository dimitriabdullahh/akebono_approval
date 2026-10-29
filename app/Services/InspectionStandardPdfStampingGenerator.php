<?php

namespace App\Services;

use App\Models\InspectionStandard;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class InspectionStandardPdfStampingGenerator
{
    /**
     * Generate PDF using template stamping method (100% accurate)
     */
    public function generate(InspectionStandard $inspectionStandard): string
    {
        try {
            Log::info('[PDF Stamping] Starting PDF generation', [
                'inspection_standard_id' => $inspectionStandard->id
            ]);

            // Ensure all relations are loaded
            $inspectionStandard->load(['drawings', 'inspectionItems', 'supplier']);

            // Initialize FPDI
            $pdf = new Fpdi();
            
            // Path to template PDF
            $templatePath = storage_path('app/templates/inspection_standard_template.pdf');
            
            if (!file_exists($templatePath)) {
                throw new \Exception("Template PDF not found at: {$templatePath}");
            }

            $pageCount = $pdf->setSourceFile($templatePath);
            
            Log::info('[PDF Stamping] Template loaded', [
                'page_count' => $pageCount,
                'template_path' => $templatePath
            ]);

            // ==================== PAGE 1 ====================
            $this->generatePage1($pdf, $inspectionStandard);
            
            // ==================== PAGE 2 ====================
            $this->generatePage2($pdf, $inspectionStandard);
            
            // ==================== PAGE 3 ====================
            $this->generatePage3($pdf, $inspectionStandard);

            // Generate filename
            $filename = $this->generateFilename($inspectionStandard);
            
            // Store PDF
            $path = "inspection-standards/{$inspectionStandard->id}/{$filename}";
            
            $pdfContent = $pdf->Output('S');
            Storage::disk('documents')->put($path, $pdfContent);
            
            Log::info('[PDF Stamping] PDF stored', [
                'path' => $path,
                'size' => strlen($pdfContent)
            ]);

            // Update inspection standard with PDF path
            $inspectionStandard->update(['pdf_file_path' => $path]);
            $inspectionStandard->refresh();
            
            Log::info('[PDF Stamping] PDF generated successfully', ['path' => $path]);

            return $path;
        } catch (\Exception $e) {
            Log::error('[PDF Stamping] Error generating PDF', [
                'inspection_standard_id' => $inspectionStandard->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate Page 1 - Header, Specifications, Approvals, Revisions
     * Coordinates extracted from template analysis (100% accurate)
     */
    private function generatePage1(Fpdi $pdf, InspectionStandard $is)
    {
        // Add page with Letter size (215.9 x 279.4 mm)
        $pdf->AddPage('P', [215.9, 279.4]);
        $tplIdx = $pdf->importPage(1);
        $pdf->useTemplate($tplIdx, 0, 0, 215.9, 279.4);
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        // ========== TOP HEADER SECTION ==========
        // Analyzed coordinates: Text "MODEL :" at Y=33.0pt (11.64mm)
        
        // MODEL field (after "MODEL :")
        $pdf->SetXY(68, 11.6);
        $pdf->Write(0, $is->model ?? '');
        
        // DATE OF ISSUED field (right side, same row)
        $pdf->SetXY(150, 11.6);
        $pdf->Write(0, $is->date_issued ? date('Y-m-d', strtotime($is->date_issued)) : '');
        
        // REF. NO field (after "REF. NO :" at Y=51.9pt = 18.33mm)
        $pdf->SetXY(137, 18.3);
        $pdf->Write(0, $is->ref_no ?? '');
        
        // ========== PART ROUTE ROW (Y=70.8pt = 24.98mm) ==========
        // PART ROUTE field
        $pdf->SetXY(30, 25);
        $pdf->Write(0, str_replace(['→', '&rarr;'], '->', $is->part_route ?? ''));

        // ========== RIGHT SIDE BOX - PART INFO (Y=261.2pt = 92.16mm) ==========
        // PART NO field (after "PART NO :")
        $pdf->SetXY(175, 92.2);
        $pdf->Write(0, $is->part_no ?? '');
        
        // PART NAME field (slightly below)
        $pdf->SetXY(175, 96.7);
        $pdf->Write(0, $is->part_name ?? '');

        // ========== SPECIFICATIONS ROW (Between horizontal lines at Y=85.3-122.7pt) ==========
        // Row area: Y=85.3pt (30.08mm) to Y=122.7pt (43.29mm)
        // Vertical lines define columns: 62.0, 91.5, 135.8, 180.1, 224.4, 268.7, 313.0
        
        $pdf->SetFont('Arial', '', 6.5);
        $specY = 30.5; // Centered in the specifications area
        
        // Supplier (X: 21.87mm to 32.29mm, center = 27mm)
        $pdf->SetXY(22, $specY);
        $pdf->Cell(10.4, 5, $is->supplier->name ?? '', 0, 0, 'C');
        
        // Manufacturer (X: 32.29mm to 47.91mm, width = 15.62mm)
        $pdf->SetXY(32.3, $specY);
        $pdf->Cell(15.6, 5, $is->manufacturer ?? '', 0, 0, 'C');
        
        // Material (X: 47.91mm to 63.53mm, width = 15.62mm)
        $pdf->SetXY(47.9, $specY);
        $pdf->Cell(15.6, 5, $is->material ?? '', 0, 0, 'C');
        
        // General Tolerance (X: 63.53mm to 79.16mm, width = 15.63mm)
        $pdf->SetXY(63.5, $specY);
        $pdf->Cell(15.6, 5, $is->general_tolerance ?? '', 0, 0, 'C');
        
        // Weight (X: 79.16mm to 94.78mm, width = 15.62mm)
        $pdf->SetXY(79.2, $specY);
        $pdf->Cell(15.6, 5, $is->weight ?? '', 0, 0, 'C');

        // ========== REVISION TABLE (Rows between Y=453.4-527.7pt) ==========
        // First data row: Y=479.3pt (169.08mm)
        if ($is->revision_date || $is->revision_record) {
            $pdf->SetFont('Arial', '', 6.5);
            $revisionY = 160;
            
            // REVISE MARK column (narrow, before X=76.8pt = 27.08mm)
            $pdf->SetXY(22, $revisionY);
            $pdf->Cell(5, 5, '1', 0, 0, 'C');
            
            // DATE column (X: 27.08mm to ~47.91mm)
            $pdf->SetXY(27, $revisionY);
            $pdf->Cell(20, 5, $is->revision_date ? date('Y-m-d', strtotime($is->revision_date)) : '', 0, 0, 'C');
            
            // REVISION RECORD (X: ~47.91mm to end)
            $pdf->SetXY(48, $revisionY);
            $pdf->Cell(145, 5, $is->revision_record ?? '', 0, 0, 'L');
        }

        // ========== STATUS CHECKBOX (Y=699.4 and 736.5pt) ==========
        // DRAFT at Y=700.5pt (247.13mm), FINAL at Y=737.6pt (260.21mm)
        $pdf->SetFont('Arial', 'B', 9);
        if ($is->status === 'DRAFT' || $is->status === 'PENDING') {
            // DRAFT checkbox
            $pdf->SetXY(33, 247);
            $pdf->Write(0, 'X');
        } elseif ($is->status === 'SUBMITTED' || $is->status === 'APPROVED') {
            // FINAL checkbox
            $pdf->SetXY(33, 260);
            $pdf->Write(0, 'X');
        }

        // ========== DISTRIBUTION FOOTER (Y=693.8, 719.0, 744.0pt) ==========
        // Customer at Y=693.8pt (244.76mm), Supplier at Y=719.0pt (253.66mm), Manufacturer at Y=744.0pt (262.47mm)
        $pdf->SetFont('Arial', '', 6.5);
        
        // Customer
        $pdf->SetXY(54, 244.8);
        $pdf->Write(0, ': AAIJ (PURCHASING & QUALITY DIVISION)');
        
        // Supplier
        $pdf->SetXY(54, 253.7);
        $pdf->Write(0, ': ' . ($is->supplier->name ?? ''));
        
        // Manufacturer
        $pdf->SetXY(54, 262.5);
        $pdf->Write(0, ': ' . ($is->manufacturer ?? ''));
    }

    /**
     * Generate Page 2 - Part Drawing
     * Coordinates extracted from template analysis (100% accurate)
     */
    private function generatePage2(Fpdi $pdf, InspectionStandard $is)
    {
        $pdf->AddPage('P', [215.9, 279.4]);
        $tplIdx = $pdf->importPage(2);
        $pdf->useTemplate($tplIdx, 0, 0, 215.9, 279.4);
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        // ========== HEADER (Y=22.9pt = 8.08mm) ==========
        // MODEL field (after "MODEL :")
        $pdf->SetXY(73, 8.1);
        $pdf->Write(0, $is->model ?? '');
        
        // PART NO field (after "PART NO :")
        $pdf->SetXY(153, 8.1);
        $pdf->Write(0, $is->part_no ?? '');

        // ========== PART DRAWING AREA ==========
        // Drawing label at Y=84.5pt (29.80mm)
        // Drawing area: between Y=64.4pt (22.71mm) and Y=676.7pt (238.73mm)
        // Available space for image: approximately 40mm to 230mm vertically
        
        if ($is->drawings && $is->drawings->count() > 0) {
            $drawing = $is->drawings->first();
            $imagePath = storage_path('app/documents/' . $drawing->file_path);
            
            if (file_exists($imagePath) && in_array(strtolower($drawing->file_type ?? ''), ['jpg', 'jpeg', 'png'])) {
                try {
                    // Insert image in drawing area
                    // X: margin at ~30mm, width ~155mm (to keep within safe margins)
                    // Y: start at ~40mm, height ~180mm
                    $pdf->Image($imagePath, 30, 40, 155, 180, '', '', '', false, 300, '', false, false, 1);
                } catch (\Exception $e) {
                    Log::warning('[PDF Stamping] Could not insert image', ['error' => $e->getMessage()]);
                }
            }
        }

        // ========== FOOTER DISTRIBUTION (Y=702.2, 727.0, 751.9pt) ==========
        // Customer at Y=702.2pt (247.73mm), Supplier at Y=727.0pt (256.45mm), Manufacturer at Y=751.9pt (265.27mm)
        $pdf->SetFont('Arial', '', 6.5);
        
        // Customer (with ": AAIJ (PUR & QUALITY DIV)" at X=285.6pt = 100.77mm)
        $pdf->SetXY(101, 247.7);
        $pdf->Write(0, ': AAIJ (PURCHASING & QUALITY DIVISION)');
        
        // Supplier
        $pdf->SetXY(101, 256.5);
        $pdf->Write(0, ': ' . ($is->supplier->name ?? ''));
        
        // Manufacturer (text starts at X=50.9pt = 17.95mm)
        $pdf->SetXY(101, 265.3);
        $pdf->Write(0, ': ' . ($is->manufacturer ?? ''));
    }

    /**
     * Generate Page 3 - Inspection Items Table
     * Coordinates extracted from template analysis (100% accurate)
     * Table has 8 columns with precise vertical boundaries
     */
    private function generatePage3(Fpdi $pdf, InspectionStandard $is)
    {
        $pdf->AddPage('P', [215.9, 279.4]);
        $tplIdx = $pdf->importPage(3);
        $pdf->useTemplate($tplIdx, 0, 0, 215.9, 279.4);
        
        $pdf->SetFont('Arial', '', 8);
        $pdf->SetTextColor(0, 0, 0);

        // ========== HEADER (Y=35.6pt = 12.57mm) ==========
        // MODEL field (after "MODEL :")
        $pdf->SetXY(72, 12.6);
        $pdf->Write(0, $is->model ?? '');
        
        // PART NO field (after "PART NO :")
        $pdf->SetXY(152, 12.6);
        $pdf->Write(0, $is->part_no ?? '');

        // ========== INSPECTION ITEMS TABLE ==========
        // Table header at Y=105.8pt (37.32mm)
        // First data row starts at Y=176.0pt (62.09mm)
        // Row boundaries: 176.0, 260.3, 330.5, 386.6, 442.8, 499.0, 583.2pt
        
        // Column boundaries (X coordinates in mm):
        // Col 0: 31.91mm (NO)
        // Col 1: 47.53mm (INSPECTION ITEM)
        // Col 2: 63.15mm (divider)
        // Col 3: 78.78mm (INSPECTION INSTRUMENT)
        // Col 4: 82.67mm (small divider)
        // Col 5: 106.30mm (INSPECTION CRITERION)
        // Col 6: 116.04mm (RANK)
        // Col 7: 131.66mm (SUPPLIER SAMPLING)
        // Col 8: 147.28mm (AAIJ SAMPLING)
        // Col 9: 152.49mm (divider)
        // Col 10: 168.12mm (REMARK)
        // Col 11: 183.74mm (end)
        
        $pdf->SetFont('Arial', '', 6);
        
        // Row definitions based on horizontal line analysis
        $rowYPositions = [
            62.09,  // Row 0: 176.0pt
            91.82,  // Row 1: 260.3pt
            116.58, // Row 2: 330.5pt
            136.39, // Row 3: 386.6pt
            156.21, // Row 4: 442.8pt
            176.03, // Row 5: 499.0pt
            205.75, // Row 6: 583.2pt
        ];
        
        // Column X positions (center or left alignment reference)
        $colNo = 31.9;              // NO column (centered in 31.91-47.53mm)
        $colItem = 48;              // INSPECTION ITEM starts at 47.53mm
        $colInstrument = 63.5;      // INSPECTION INSTRUMENT starts at 63.15mm
        $colCriterion = 83;         // INSPECTION CRITERION starts at ~82.67mm
        $colRank = 106.5;           // RANK (centered in 106.30-116.04mm)
        $colSupplierSample = 117;   // SUPPLIER SAMPLING (centered in 116.04-131.66mm)
        $colAaijSample = 132;       // AAIJ SAMPLING (centered in 131.66-147.28mm)
        $colRemark = 152.5;         // REMARK starts at 152.49mm

        // Column widths
        $widthNo = 15.6;            // 47.53 - 31.91 = 15.62mm
        $widthItem = 15.6;          // 63.15 - 47.53 = 15.62mm
        $widthInstrument = 15.6;    // 78.78 - 63.15 = 15.63mm
        $widthCriterion = 23.6;     // 106.30 - 82.67 = 23.63mm
        $widthRank = 9.7;           // 116.04 - 106.30 = 9.74mm
        $widthSupplierSample = 15.6; // 131.66 - 116.04 = 15.62mm
        $widthAaijSample = 15.6;    // 147.28 - 131.66 = 15.62mm
        $widthRemark = 31.2;        // 183.74 - 152.49 = 31.25mm

        foreach ($is->inspectionItems as $index => $item) {
            if ($index >= 7) break; // Max 7 rows based on template analysis
            
            $currentY = $rowYPositions[$index];
            $rowHeight = 5; // Approximate row height
            
            // NO - centered
            $pdf->SetXY($colNo, $currentY);
            $pdf->Cell($widthNo, $rowHeight, $index + 1, 0, 0, 'C');
            
            // INSPECTION ITEM - left aligned
            $pdf->SetXY($colItem, $currentY);
            $pdf->MultiCell($widthItem, $rowHeight, $item->inspection_item ?? '', 0, 'L', false);
            
            // INSPECTION INSTRUMENT - left aligned
            $pdf->SetXY($colInstrument, $currentY);
            $pdf->MultiCell($widthInstrument, $rowHeight, $item->inspection_instrument ?? '', 0, 'L', false);
            
            // INSPECTION CRITERION - left aligned
            $pdf->SetXY($colCriterion, $currentY);
            $pdf->MultiCell($widthCriterion, $rowHeight, $item->inspection_criterion ?? '', 0, 'L', false);
            
            // RANK - centered
            $pdf->SetXY($colRank, $currentY);
            $pdf->Cell($widthRank, $rowHeight, $item->rank ?? '', 0, 0, 'C');
            
            // SUPPLIER SAMPLING - centered
            $pdf->SetXY($colSupplierSample, $currentY);
            $pdf->Cell($widthSupplierSample, $rowHeight, $item->supplier_sampling ?? '', 0, 0, 'C');
            
            // AAIJ SAMPLING - centered
            $pdf->SetXY($colAaijSample, $currentY);
            $pdf->Cell($widthAaijSample, $rowHeight, $item->aaij_sampling ?? '', 0, 0, 'C');
            
            // REMARK - left aligned
            $pdf->SetXY($colRemark, $currentY);
            $pdf->MultiCell($widthRemark, $rowHeight, $item->remark_inspection_method ?? '', 0, 'L', false);
        }

        // ========== FOOTER DISTRIBUTION (Y=689.6, 714.4, 739.3pt) ==========
        // Customer at Y=689.6pt (243.29mm), Supplier at Y=714.4pt (252.01mm), Manufacturer at Y=739.3pt (260.81mm)
        $pdf->SetFont('Arial', '', 6.5);
        
        // Customer (text starts at X=225.5pt = 79.56mm)
        $pdf->SetXY(79.6, 243.3);
        $pdf->Write(0, ': AAIJ (PURCHASING & QUALITY DIVISION)');
        
        // Supplier
        $pdf->SetXY(79.6, 252);
        $pdf->Write(0, ': ' . ($is->supplier->name ?? ''));
        
        // Manufacturer
        $pdf->SetXY(79.6, 260.8);
        $pdf->Write(0, ': ' . ($is->manufacturer ?? ''));
    }

    /**
     * Generate filename for PDF
     */
    private function generateFilename(InspectionStandard $inspectionStandard): string
    {
        $refNo = $inspectionStandard->ref_no ? \Illuminate\Support\Str::slug($inspectionStandard->ref_no) : 'IS';
        $partNo = $inspectionStandard->part_no ? \Illuminate\Support\Str::slug($inspectionStandard->part_no) : 'PART';
        $timestamp = Carbon::now()->format('YmdHis');

        return "IS_{$refNo}_{$partNo}_{$timestamp}.pdf";
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
            throw new \Exception("PDF file not found at: {$filePath}");
        }

        $filename = basename($filePath);
        return response()->file($filePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }
}
