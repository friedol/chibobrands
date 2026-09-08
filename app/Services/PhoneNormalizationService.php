<?php

namespace App\Services;

class PhoneNormalizationService
{
    /**
     * Standardize a phone number to international format (e.g., +255687123456).
     *
     * @param string|null $phone
     * @param string $defaultCountryCode
     * @return string|null
     */
    public static function normalize(?string $phone, string $defaultCountryCode = '255'): ?string
    {
        if (empty($phone)) {
            return null;
        }

        // Remove all characters except digits and plus sign
        $clean = preg_replace('/[^\d+]/', '', trim($phone));

        if (empty($clean)) {
            return null;
        }

        // If number starts with +, return cleaned number
        if (str_starts_with($clean, '+')) {
            return $clean;
        }

        // If number starts with leading 0 (e.g. 0687123456 or 0753883382)
        if (str_starts_with($clean, '0')) {
            return '+' . $defaultCountryCode . substr($clean, 1);
        }

        // If number starts with country code without plus (e.g. 255687123456)
        if (str_starts_with($clean, $defaultCountryCode)) {
            return '+' . $clean;
        }

        // Default fallback for 9-digit numbers without leading zero (e.g., 687123456)
        if (strlen($clean) === 9) {
            return '+' . $defaultCountryCode . $clean;
        }

        // Fallback: prefix with plus if valid digits
        return '+' . $clean;
    }
}
