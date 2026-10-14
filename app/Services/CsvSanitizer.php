<?php

namespace App\Services;

class CsvSanitizer
{
    /**
     * Dangerous formula trigger characters recognized by spreadsheet applications (Excel, Calc, Google Sheets).
     *
     * @var array<int, string>
     */
    public const FORMULA_TRIGGERS = ['=', '+', '-', '@', '|', '%'];

    /**
     * Sanitize a single CSV cell value against CSV / Spreadsheet formula injection (CWE-1236 / OWASP).
     *
     * Preserves:
     * - Null and empty values (returns empty string)
     * - Pure numeric values (integers, floats, valid negative numbers e.g. -15, -0.5, 42, +62812345678)
     * - Single dash placeholder '-' used for missing/unfilled data
     * - Plain text and Unicode (e.g. Indonesian names, Japanese characters)
     *
     * Sanitizes:
     * - Any non-numeric string whose first non-whitespace/non-control character is a formula trigger (=, +, -, @, |, %)
     * - Any string starting with tab (\t) or carriage return (\r)
     *
     * Prepends a single quote (') at index 0 to force spreadsheet programs to treat the cell as literal text.
     */
    public static function sanitize(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        $str = (string) $value;

        if ($str === '') {
            return '';
        }

        $trimmed = trim($str);

        // Preserve empty/whitespace-only values
        if ($trimmed === '') {
            return $str;
        }

        // Single dash placeholder (e.g. '-' used for empty fields in immigration tables)
        if ($trimmed === '-') {
            return $str;
        }

        // Legitimate numeric values (e.g. -15, -0.5, 42, +62812345678)
        // A pure numeric string cannot execute formulas, DDE commands, or external macros.
        if (is_numeric($trimmed)) {
            return $str;
        }

        // Check if string starts with raw tab or carriage return
        if (preg_match('/^[\t\r]/', $str) === 1) {
            return "'".$str;
        }

        // Check if leading whitespace or ASCII control characters (\x00-\x1F, \x7F) are followed by a formula trigger
        if (preg_match('/^[\s\x00-\x1F\x7F]*[=\+\-@|%]/u', $str) === 1) {
            return "'".$str;
        }

        return $str;
    }

    /**
     * Sanitize an entire row array for CSV output.
     *
     * @param  array<int|string, mixed>  $row
     * @return array<int|string, string>
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map(fn (mixed $val): string => self::sanitize($val), $row);
    }
}
