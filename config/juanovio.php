<?php

return [
    'inquiries_enabled' => env('INQUIRIES_ENABLED', false),
    'recipient' => env('INQUIRY_RECIPIENT'),
    'legal_reviewed' => env('LEGAL_REVIEWED', false),
    'logo_path' => env('BRAND_LOGO_PATH'),
    'brand_assets' => [
        'horizontal' => ['path' => 'brand/optimized/juanovio-logo-horizontal-640.webp', 'width' => 640, 'height' => 480, 'variants' => [640, 960], 'base' => 'juanovio-logo-horizontal'],
        'full' => ['path' => 'brand/optimized/juanovio-logo-full-480.webp', 'width' => 480, 'height' => 360, 'variants' => [480, 720], 'base' => 'juanovio-logo-full'],
        'monogram' => ['path' => 'brand/optimized/juanovio-monogram-96.webp', 'width' => 96, 'height' => 96, 'variants' => [96, 192], 'base' => 'juanovio-monogram'],
    ],
    'navigation' => ['home' => 'Home', 'about' => 'About', 'trade' => 'What We Trade', 'how-we-work' => 'How We Work', 'markets' => 'Markets', 'contact' => 'Contact'],
    'types' => ['buy' => 'Buying inquiry', 'supply' => 'Supplier introduction', 'partnership' => 'Partnership', 'general' => 'General inquiry'],
    'categories' => [
        'construction' => ['title' => 'Construction & Infrastructure Materials', 'text' => 'Cement and other construction inputs may form part of sourcing discussions, depending on specifications, origin, and market needs. Initial pilot activity is exploring cement opportunities.'],
        'agriculture-food' => ['title' => 'Agriculture & Food Commodities', 'text' => 'Potential opportunities include flour and other agricultural or food products, subject to applicable standards, origin requirements, shelf life, documentation, and demand.'],
        'other' => ['title' => 'Other Trade Opportunities', 'text' => 'We remain open to evaluating additional lawful commercial goods where buyer needs and qualified supply align.'],
    ],
    'markets' => [
        'guyana' => ['title' => 'Initial focus', 'text' => 'Guyana is an initial destination market being evaluated for cement-related opportunities. This is a pilot focus, not the full geographic scope of Juanovio.', 'status' => 'Under evaluation'],
        'additional' => ['title' => 'Expanding opportunities', 'text' => 'We welcome discussions involving other markets, subject to demand, qualified counterparties, trade restrictions, logistics feasibility, and commercial fit.', 'status' => 'Open to discussion'],
    ],
];
