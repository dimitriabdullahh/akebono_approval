<?php

namespace App\Helpers;

/**
 * Helper untuk parse dan format Specification values
 * Format: "angka±angka" (e.g., "3,5±0,2" atau "3.5±0.2")
 */
class SpecificationHelper
{
    /**
     * Parse specification string menjadi array dengan nominal dan tolerance
     * 
     * @param string|null $specs Specification string (e.g., "3,5±0,2")
     * @return array ['nominal' => float, 'tolerance' => float, 'min' => float, 'max' => float, 'raw' => string] or null if invalid
     */
    public static function parseSpec(?string $specs): ?array
    {
        if (empty($specs)) {
            return null;
        }

        $specs = trim($specs);
        
        // Pattern: "number±number" atau "number ± number"
        // Support both comma (,) and dot (.) as decimal separator
        $pattern = '/^([\d,.\s]+)[±±]([\d,.\s]+)$/u';
        
        if (!preg_match($pattern, $specs, $matches)) {
            return null;
        }

        $nominal = self::parseNumber($matches[1]);
        $tolerance = self::parseNumber($matches[2]);

        if ($nominal === null || $tolerance === null) {
            return null;
        }

        return [
            'nominal' => $nominal,
            'tolerance' => $tolerance,
            'min' => $nominal - $tolerance,
            'max' => $nominal + $tolerance,
            'raw' => $specs,
        ];
    }

    /**
     * Parse single number string (support comma and dot as decimal)
     * 
     * @param string $str Number string
     * @return float|null Parsed number or null if invalid
     */
    private static function parseNumber(string $str): ?float
    {
        $str = trim($str);
        // Replace comma with dot untuk consistent parsing
        $str = str_replace(',', '.', $str);
        
        if (!is_numeric($str)) {
            return null;
        }
        
        return (float) $str;
    }

    /**
     * Format parsed specification menjadi string readable
     * 
     * @param array $parsed Result dari parseSpec()
     * @param int $decimalPlaces Jumlah decimal places untuk display
     * @return string Formatted specification (e.g., "3.50 ± 0.20")
     */
    public static function formatParsed(array $parsed, int $decimalPlaces = 2): string
    {
        return sprintf(
            '%s ± %s',
            number_format($parsed['nominal'], $decimalPlaces, '.', ''),
            number_format($parsed['tolerance'], $decimalPlaces, '.', '')
        );
    }

    /**
     * Format specification string langsung
     * Normalisasi ke format: "nominal ± tolerance"
     * 
     * @param string|null $specs Specification string
     * @param int $decimalPlaces Jumlah decimal places
     * @return string|null Formatted specification atau null jika invalid
     */
    public static function format(?string $specs, int $decimalPlaces = 2): ?string
    {
        $parsed = self::parseSpec($specs);
        return $parsed ? self::formatParsed($parsed, $decimalPlaces) : null;
    }

    /**
     * Get min value dari specification
     * 
     * @param string|null $specs Specification string
     * @return float|null Min value atau null jika invalid
     */
    public static function getMin(?string $specs): ?float
    {
        $parsed = self::parseSpec($specs);
        return $parsed ? $parsed['min'] : null;
    }

    /**
     * Get max value dari specification
     * 
     * @param string|null $specs Specification string
     * @return float|null Max value atau null jika invalid
     */
    public static function getMax(?string $specs): ?float
    {
        $parsed = self::parseSpec($specs);
        return $parsed ? $parsed['max'] : null;
    }

    /**
     * Get nominal (center) value dari specification
     * 
     * @param string|null $specs Specification string
     * @return float|null Nominal value atau null jika invalid
     */
    public static function getNominal(?string $specs): ?float
    {
        $parsed = self::parseSpec($specs);
        return $parsed ? $parsed['nominal'] : null;
    }

    /**
     * Get tolerance value dari specification
     * 
     * @param string|null $specs Specification string
     * @return float|null Tolerance value atau null jika invalid
     */
    public static function getTolerance(?string $specs): ?float
    {
        $parsed = self::parseSpec($specs);
        return $parsed ? $parsed['tolerance'] : null;
    }

    /**
     * Validate specification format
     * 
     * @param string|null $specs Specification string
     * @return bool True jika format valid
     */
    public static function isValid(?string $specs): bool
    {
        return self::parseSpec($specs) !== null;
    }

    /**
     * Get formatted specification dengan range info
     * e.g., "3.50 ± 0.20 (Min: 3.30, Max: 3.70)"
     * 
     * @param string|null $specs Specification string
     * @param int $decimalPlaces Jumlah decimal places
     * @return string|null Formatted string dengan range info
     */
    public static function formatWithRange(?string $specs, int $decimalPlaces = 2): ?string
    {
        $parsed = self::parseSpec($specs);
        if (!$parsed) {
            return null;
        }

        $formatted = self::formatParsed($parsed, $decimalPlaces);
        $min = number_format($parsed['min'], $decimalPlaces, '.', '');
        $max = number_format($parsed['max'], $decimalPlaces, '.', '');

        return sprintf(
            '%s (Min: %s, Max: %s)',
            $formatted,
            $min,
            $max
        );
    }
}
