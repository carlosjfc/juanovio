<?php

return [
    'analytics' => [
        'enabled' => env('ANALYTICS_ENABLED', env('APP_ENV') === 'production'),
        'driver' => env('ANALYTICS_DRIVER', 'gtm'),
        'ga4_id' => env('GOOGLE_ANALYTICS_ID', 'G-Y1RN3SLGFQ'),
        'gtm_id' => env('GOOGLE_TAG_MANAGER_ID', 'GTM-TFMSHHLF'),
        'consent_version' => env('ANALYTICS_CONSENT_VERSION', '2026-10-11'),
        'consent_max_age_days' => 180,
    ],
    'inquiries_enabled' => env('INQUIRIES_ENABLED', false),
    'recipient' => env('INQUIRY_RECIPIENT'),
    'legal_reviewed' => env('LEGAL_REVIEWED', false),
    'logo_path' => env('BRAND_LOGO_PATH'),
    'social_image' => [
        'path' => 'brand/original/juanovio-logo-full.png',
        'width' => 1448,
        'height' => 1086,
        'type' => 'image/png',
        'alt' => 'Juanovio International Trading Inc. — approved company logo',
    ],
    'brand_assets' => [
        'horizontal' => ['path' => 'brand/optimized/juanovio-logo-horizontal-640.webp', 'width' => 640, 'height' => 480, 'variants' => [640, 960], 'base' => 'juanovio-logo-horizontal'],
        'full' => ['path' => 'brand/optimized/juanovio-logo-full-480.webp', 'width' => 480, 'height' => 360, 'variants' => [480, 720], 'base' => 'juanovio-logo-full'],
        'monogram' => ['path' => 'brand/optimized/juanovio-monogram-96.webp', 'width' => 96, 'height' => 96, 'variants' => [96, 192], 'base' => 'juanovio-monogram'],
    ],
    'navigation' => ['home' => 'Home', 'about' => 'About', 'trade' => 'What We Trade', 'how-we-work' => 'How We Work', 'markets' => 'Markets', 'contact' => 'Contact'],
    'types' => ['buy' => 'Buying inquiry', 'supply' => 'Supplier introduction', 'partnership' => 'Partnership', 'general' => 'General inquiry'],
    'quantity_units' => [
        'bags' => 'Bags',
        'mt' => 'Metric tonnes (MT)',
        'kg' => 'Kilograms',
        'litres' => 'Litres',
        'units' => 'Units',
        'other' => 'Other',
    ],
    'categories' => [
        'construction' => ['title' => 'Construction & Infrastructure Materials', 'text' => 'Cement is our current commercial development priority. We evaluate sourcing and destination-market opportunities according to product specifications, manufacturing origin, quantity, documentation, and logistics. Other construction inputs are considered when they match a defined buyer requirement.'],
        'agriculture-food' => ['title' => 'Agriculture & Food Commodities', 'text' => 'Flour and other selected agricultural or food products are areas of exploratory interest. Opportunities are reviewed according to buyer demand, applicable food standards, origin requirements, shelf life, and documentation.'],
        'other' => ['title' => 'Other Trade Opportunities', 'text' => 'We consider additional commercial goods when there is a clear buyer requirement and suitable supply. Product suitability, applicable requirements, and commercial feasibility guide the evaluation.'],
    ],
    'markets' => [
        'sourcing' => [
            'title' => 'Sourcing Markets',
            'text' => 'We consider supply opportunities in the following countries, reviewing the product\'s actual manufacturing origin, specifications, export documentation, and commercial terms for each inquiry.',
            'countries' => ['DO' => 'Dominican Republic', 'PA' => 'Panama', 'GT' => 'Guatemala', 'CO' => 'Colombia', 'BR' => 'Brazil', 'TR' => 'Türkiye (Turkey)'],
        ],
        'destination' => [
            'title' => 'Destination Markets',
            'text' => 'Our market development includes the following destinations. We welcome inquiries from importers, distributors, and business buyers with defined sourcing requirements.',
            'countries' => ['GY' => 'Guyana', 'JM' => 'Jamaica', 'SR' => 'Suriname', 'BS' => 'Bahamas', 'BZ' => 'Belize', 'GD' => 'Grenada', 'AG' => 'Antigua and Barbuda'],
        ],
    ],
];
