<?php

namespace App\Services;

use App\Models\MsaGageRr;

/**
 * Service untuk validasi MSA Gage R&R form
 * Memastikan data collection sheet (green table) terisi semua
 */
class MsaValidationService
{
    /**
     * Validasi apakah green table (data collection) terisi lengkap
     * 
     * @param array $measurements Data measurements dari form
     * @param int $appraisers Jumlah appraiser (2 atau 3)
     * @param int $trials Jumlah trial per appraiser (2 atau 3)
     * @param int $parts Jumlah part (1-10)
     * @return array ['is_valid' => bool, 'errors' => string[], 'missing_cells' => int]
     */
    public static function validateDataCollection(
        array $measurements,
        int $appraisers = 3,
        int $trials = 3,
        int $parts = 10
    ): array
    {
        $errors = [];
        $missingCells = 0;

        // Appraiser labels: A, B, C
        $appraiserLabels = ['A', 'B', 'C'];

        // Only validate active appraisers (based on settings)
        $activeAppraiserLabels = array_slice($appraiserLabels, 0, $appraisers);

        // Loop through all required cells
        foreach ($activeAppraiserLabels as $appraiserKey) {
            // Check if appraiser data exists
            if (!isset($measurements[$appraiserKey])) {
                $errors[] = sprintf('Data Appraiser %s tidak ditemukan', $appraiserKey);
                $missingCells += $trials * $parts;
                continue;
            }

            for ($t = 0; $t < $trials; $t++) { // Trial index: 0, 1, 2
                // Check if trial data exists
                if (!isset($measurements[$appraiserKey][$t])) {
                    $errors[] = sprintf('Data Appraiser %s, Trial %d tidak ditemukan', $appraiserKey, $t + 1);
                    $missingCells += $parts;
                    continue;
                }

                for ($p = 0; $p < $parts; $p++) { // Part index: 0-9
                    // Access using string key: measurements['A'][trial_index][part_index]
                    $value = $measurements[$appraiserKey][$t][$p] ?? null;

                    // Check if value is empty or not numeric
                    if ($value === null || $value === '' || !is_numeric($value)) {
                        $missingCells++;
                        $errors[] = sprintf(
                            'Appraiser %s, Trial %d, Part %d - belum terisi',
                            $appraiserKey,
                            $t + 1, // Display as 1-indexed for user
                            $p + 1  // Display as 1-indexed for user
                        );
                    }
                }
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'missing_cells' => $missingCells,
            'total_cells' => $appraisers * $trials * $parts,
            'filled_cells' => ($appraisers * $trials * $parts) - $missingCells,
        ];
    }

    /**
     * Validasi MSA form sebelum submission
     * Checks: specifications, general info, dan data collection
     * 
     * @param array $data Form data dari request
     * @return array ['is_valid' => bool, 'errors' => string[], 'warnings' => string[]]
     */
    public static function validateBeforeSubmission(array $data): array
    {
        $errors = [];
        $warnings = [];

        // 1. Validate general information
        $general = $data['generalInformation'] ?? [];

        if (empty($general['date'])) {
            $errors[] = 'Date harus diisi';
        }

        if (empty($general['characteristics'])) {
            $errors[] = 'Characteristics harus diisi';
        }

        if (empty($general['specifications'])) {
            $errors[] = 'Specifications harus diisi';
        } else {
            // Validate specifications format - make it more permissive
            $specs = trim($general['specifications']);
            // Check if it contains ± symbol (either regular or unicode)
            if (strpos($specs, '±') === false && strpos($specs, '±') === false) {
                $errors[] = 'Format Specifications harus mengandung simbol ± (contoh: 3.5±0.2 atau 10±1)';
            } elseif (!self::isValidSpecificationFormat($specs)) {
                $errors[] = 'Format Specifications tidak valid. Gunakan format: angka±angka (contoh: 3.5±0.2)';
            }
        }

        if (empty($general['gageName'])) {
            $errors[] = 'Gage Name harus diisi';
        }

        if (empty($general['gageNo'])) {
            $warnings[] = 'Gage No belum diisi (optional)';
        }

        if (empty($general['gageType'])) {
            $errors[] = 'Gage Type harus diisi';
        }

        if (empty($general['performedBy'])) {
            $errors[] = 'Performed By harus diisi';
        }

        // 2. Validate settings
        $settings = $data['settings'] ?? [];
        $appraisers = $settings['appraisers'] ?? 3;
        $trials = $settings['trials'] ?? 3;
        $parts = $settings['parts'] ?? 10;

        if (!in_array($appraisers, [2, 3])) {
            $errors[] = 'Jumlah appraiser harus 2 atau 3';
        }

        if (!in_array($trials, [2, 3])) {
            $errors[] = 'Jumlah trial harus 2 atau 3';
        }

        if ($parts < 2 || $parts > 10) {
            $errors[] = 'Jumlah part harus antara 2-10';
        }

        // 3. Validate data collection (green table)
        $measurements = $data['measurements'] ?? [];
        $dataValidation = self::validateDataCollection($measurements, $appraisers, $trials, $parts);

        if (!$dataValidation['is_valid']) {
            $errors[] = sprintf(
                'Data Collection Sheet belum lengkap: %d dari %d cell masih kosong',
                $dataValidation['missing_cells'],
                $dataValidation['total_cells']
            );

            // Add first 5 missing cells untuk detail info
            $missingDetails = array_slice($dataValidation['errors'], 0, 5);
            foreach ($missingDetails as $detail) {
                $errors[] = '  • ' . $detail;
            }

            if (count($dataValidation['errors']) > 5) {
                $errors[] = '  ... dan ' . (count($dataValidation['errors']) - 5) . ' cell lainnya';
            }
        }

        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate specification format
     * Format: "number±number" (support comma dan dot)
     * 
     * @param string $specs Specification string
     * @return bool
     */
    private static function isValidSpecificationFormat(string $specs): bool
    {
        $pattern = '/^([\d,.\s]+)[±±]([\d,.\s]+)$/u';
        return (bool) preg_match($pattern, trim($specs));
    }

    /**
     * Get MSA form completion percentage
     * 
     * @param MsaGageRr $msa
     * @return int Percentage (0-100)
     */
    public static function getCompletionPercentage(MsaGageRr $msa): int
    {
        $general = $msa->general ?? [];
        $settings = $msa->settings ?? [];
        $measurements = $msa->measurements ?? [];

        $totalFields = 10;
        $filledFields = 0;

        // Check general fields
        if (!empty($general['date'])) $filledFields++;
        if (!empty($general['characteristics'])) $filledFields++;
        if (!empty($general['specifications'])) $filledFields++;
        if (!empty($general['gageName'])) $filledFields++;
        if (!empty($general['gageType'])) $filledFields++;
        if (!empty($general['performedBy'])) $filledFields++;

        // Check settings
        if (!empty($settings['appraisers'])) $filledFields++;
        if (!empty($settings['trials'])) $filledFields++;
        if (!empty($settings['parts'])) $filledFields++;

        // Check if measurements exist
        if (!empty($measurements)) $filledFields++;

        return (int) (($filledFields / $totalFields) * 100);
    }

    /**
     * Format validation errors untuk display di UI
     * 
     * @param array $validation Result dari validateBeforeSubmission()
     * @return string HTML formatted error message
     */
    public static function formatErrorsForDisplay(array $validation): string
    {
        if (empty($validation['errors'])) {
            return '';
        }

        $html = '<div class="alert alert-danger" role="alert">';
        $html .= '<h5 class="alert-heading"><i class="ti ti-alert-circle me-2"></i>Validation Errors</h5>';
        $html .= '<ul class="mb-0">';

        foreach ($validation['errors'] as $error) {
            $html .= '<li>' . htmlspecialchars($error) . '</li>';
        }

        $html .= '</ul></div>';

        if (!empty($validation['warnings'])) {
            $html .= '<div class="alert alert-warning" role="alert">';
            $html .= '<h6 class="alert-heading mb-2"><i class="ti ti-alert-triangle me-2"></i>Warnings</h6>';
            $html .= '<ul class="mb-0">';

            foreach ($validation['warnings'] as $warning) {
                $html .= '<li>' . htmlspecialchars($warning) . '</li>';
            }

            $html .= '</ul></div>';
        }

        return $html;
    }
}
