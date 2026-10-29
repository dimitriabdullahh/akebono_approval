<?php

namespace App\Http\Controllers;

use App\Models\InspectionStandard;
use App\Services\InspectionStandardPdfGenerator;
use Illuminate\Support\Facades\Log;

class DevToolsController extends Controller
{
    /**
     * Generate PDF for first available Inspection Standard
     */
    public function generateInspectionStandardPdf()
    {
        try {
            // Get first available IS
            $is = InspectionStandard::with(['drawings', 'inspectionItems', 'supplier'])->first();
            
            if (!$is) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No Inspection Standard found in database'
                ], 404);
            }
            
            Log::info('Generating IS PDF', ['id' => $is->id, 'part_name' => $is->part_name]);
            
            // Generate PDF
            $generator = new InspectionStandardPdfGenerator();
            $path = $generator->generate($is);
            
            return response()->json([
                'status' => 'success',
                'message' => 'PDF generated successfully',
                'is_id' => $is->id,
                'part_name' => $is->part_name,
                'pdf_path' => $path,
                'view_url' => route('inspection-standard.pdf', $is->id)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error generating IS PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Error generating PDF: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Clear all IS PDF paths from database
     */
    public function clearInspectionStandardPdfs()
    {
        try {
            $updated = InspectionStandard::query()->update(['pdf_file_path' => null]);
            
            return response()->json([
                'status' => 'success',
                'message' => "Cleared pdf_file_path for {$updated} records"
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
