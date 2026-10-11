<?php

namespace App\Support;

class Analytics
{
    public static function settings(): ?array
    {
        $settings = config('juanovio.analytics');
        $driver = $settings['driver'];
        $id = match ($driver) {
            'gtm' => $settings['gtm_id'],
            'gtag' => $settings['ga4_id'],
            default => null,
        };
        $pattern = $driver === 'gtm' ? '/^GTM-[A-Z0-9]+$/' : '/^G-[A-Z0-9]+$/';

        if (! $settings['enabled'] || ! is_string($id) || ! preg_match($pattern, $id)) {
            return null;
        }

        return ['driver' => $driver, 'id' => $id, 'version' => $settings['consent_version'], 'maxAgeDays' => $settings['consent_max_age_days']];
    }
}
