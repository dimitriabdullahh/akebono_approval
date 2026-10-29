<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;

class SignatureQrService
{
    /**
     * Generate QR code for e-signature
     * 
     * @param string $npk Employee NPK
     * @param string $name Employee name
     * @param string $roleLabel Role label (e.g., "Checker", "Approver")
     * @return string Base64 data URI (data:image/png;base64,...)
     */
    public function generate(string $npk, string $name, string $roleLabel): string
    {
        // Format the payload exactly as specified
        $payload = "NPK: {$npk}\nNama: {$name}\nRole: {$roleLabel}";
        
        // Generate QR code
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(150) // 150px size
            ->margin(5) // Small margin
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        // Get PNG content as base64 data URI
        $dataUri = $result->getDataUri();
        
        return $dataUri;
    }

    /**
     * Generate QR code and return raw PNG binary
     * 
     * @param string $npk Employee NPK
     * @param string $name Employee name
     * @param string $roleLabel Role label
     * @return string Raw PNG binary data
     */
    public function generateBinary(string $npk, string $name, string $roleLabel): string
    {
        $payload = "NPK: {$npk}\nNama: {$name}\nRole: {$roleLabel}";
        
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size(150)
            ->margin(5)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        return $result->getString();
    }

    /**
     * Generate QR code with custom size
     * 
     * @param string $npk Employee NPK
     * @param string $name Employee name
     * @param string $roleLabel Role label
     * @param int $size QR code size in pixels
     * @return string Base64 data URI
     */
    public function generateWithSize(string $npk, string $name, string $roleLabel, int $size): string
    {
        $payload = "NPK: {$npk}\nNama: {$name}\nRole: {$roleLabel}";
        
        $result = Builder::create()
            ->writer(new PngWriter())
            ->data($payload)
            ->encoding(new Encoding('UTF-8'))
            ->errorCorrectionLevel(ErrorCorrectionLevel::Medium)
            ->size($size)
            ->margin(5)
            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->build();
        
        return $result->getDataUri();
    }

    /**
     * Validate QR payload format
     * 
     * @param string $payload Scanned QR payload
     * @return array|null Parsed data or null if invalid
     */
    public function validatePayload(string $payload): ?array
    {
        $lines = explode("\n", $payload);
        
        if (count($lines) !== 3) {
            return null;
        }
        
        $npk = null;
        $name = null;
        $role = null;
        
        foreach ($lines as $line) {
            if (str_starts_with($line, 'NPK: ')) {
                $npk = substr($line, 5);
            } elseif (str_starts_with($line, 'Nama: ')) {
                $name = substr($line, 6);
            } elseif (str_starts_with($line, 'Role: ')) {
                $role = substr($line, 6);
            }
        }
        
        if (!$npk || !$name || !$role) {
            return null;
        }
        
        return [
            'npk' => $npk,
            'name' => $name,
            'role' => $role,
        ];
    }
}
