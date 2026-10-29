<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\Log;

class QrCodeService
{
    /**
     * Generate QR code signature for PSW
     * 
     * @param array $data ['print_name' => '', 'title' => '', 'phone_no' => '', 'fax_no' => '']
     * @return string|null Base64 PNG image data URI or null if data incomplete
     */
    public static function generatePswSignature(array $data): ?string
    {
        $printName = trim($data['print_name'] ?? '');
        $title = trim($data['title'] ?? '');
        $phoneNo = trim($data['phone_no'] ?? '');
        $faxNo = trim($data['fax_no'] ?? '');
        
        // Require all 4 fields
        if (empty($printName) || empty($title) || empty($phoneNo) || empty($faxNo)) {
            Log::info('[QrCodeService] Missing required fields for QR generation', [
                'has_print_name' => !empty($printName),
                'has_title' => !empty($title),
                'has_phone_no' => !empty($phoneNo),
                'has_fax_no' => !empty($faxNo),
            ]);
            return null;
        }
        
        // Build plain text content for QR code
        $qrText = "Name: {$printName}\n";
        $qrText .= "Title: {$title}\n";
        $qrText .= "Phone No: {$phoneNo}\n";
        $qrText .= "Fax No: {$faxNo}";
        
        try {
            // Use Endroid QR Code library
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrText)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High) // High untuk QR kecil
                ->size(150) // Ukuran kecil tapi masih readable
                ->margin(5)  // Margin lebih kecil
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->build();
            
            // Convert to base64 data URI
            $base64 = base64_encode($result->getString());
            $dataUri = 'data:image/png;base64,' . $base64;
            
            Log::info('[QrCodeService] QR code generated successfully', [
                'text_length' => strlen($qrText),
                'image_size' => strlen($result->getString()),
            ]);
            
            return $dataUri;
            
        } catch (\Exception $e) {
            Log::error('[QrCodeService] Error generating QR code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
    
    /**
     * Build signature text for QR code
     * 
     * @param array $data
     * @return string|null
     */
    public static function buildSignatureText(array $data): ?string
    {
        $printName = trim($data['print_name'] ?? '');
        $title = trim($data['title'] ?? '');
        $phoneNo = trim($data['phone_no'] ?? '');
        $faxNo = trim($data['fax_no'] ?? '');
        
        if (empty($printName) || empty($title) || empty($phoneNo) || empty($faxNo)) {
            return null;
        }
        
        return "Name: {$printName}\nTitle: {$title}\nPhone No: {$phoneNo}\nFax No: {$faxNo}";
    }

    /**
     * Generate QR code for Packaging Standard approval signatures
     * 
     * @param string $nama Name of approver
     * @param string $npk NPK (employee ID) - optional, not used in QR code
     * @param string $jabatan Position/Title - optional, not used in QR code
     * @return string|null Base64 PNG image data URI or null if data incomplete
     */
    public static function generateApprovalQR(?string $nama, ?string $npk, ?string $jabatan): ?string
    {
        $nama = trim($nama ?? '');
        
        // Hanya require nama saja
        if (empty($nama)) {
            Log::info('[QrCodeService] Missing nama for approval QR generation');
            return null;
        }
        
        // Build plain text content for QR code dengan format "Nama: ..."
        $qrText = "Nama: {$nama}";
        
        try {
            // Use Endroid QR Code library
            $result = Builder::create()
                ->writer(new PngWriter())
                ->data($qrText)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High) // High untuk QR kecil
                ->size(150) // Ukuran kecil tapi masih readable
                ->margin(5)  // Margin lebih kecil
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
                ->build();
            
            // Convert to base64 data URI
            $base64 = base64_encode($result->getString());
            $dataUri = 'data:image/png;base64,' . $base64;
            
            Log::info('[QrCodeService] Approval QR code generated successfully', [
                'nama' => $nama,
                'text_length' => strlen($qrText),
                'image_size' => strlen($result->getString()),
            ]);
            
            return $dataUri;
            
        } catch (\Exception $e) {
            Log::error('[QrCodeService] Error generating approval QR code', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }
}
