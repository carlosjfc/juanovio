<?php

namespace App\Console\Commands;

use App\Livewire\InquiryForm;
use Illuminate\Console\Command;

class CheckWebsite extends Command
{
    protected $signature = 'website:check';

    protected $description = 'Check configured launch prerequisites without sending mail or changing settings';

    public function handle(): int
    {
        $url = parse_url((string) config('app.url'));
        $host = $url['host'] ?? '';
        $image = config('juanovio.social_image') ?? [];
        $imagePath = public_path($image['path'] ?? '');
        $dimensions = is_file($imagePath) ? getimagesize($imagePath) : false;
        $legalDrafts = collect(['privacy', 'terms'])->contains(fn ($page) => str_contains(strtolower(config('website.'.$page.'.heading')), 'draft'));

        $checks = [
            'Production environment' => app()->environment('production'),
            'Debug disabled' => ! config('app.debug'),
            'Application key configured' => filled(config('app.key')),
            'Public HTTPS origin configured' => ($url['scheme'] ?? '') === 'https'
                && $host !== '' && ! in_array($host, ['localhost', '127.0.0.1', '::1'], true)
                && ! preg_match('/\.(test|local|invalid|example)$/i', $host)
                && ! preg_match('/(^|\.)example\.(com|org|net)$/i', $host)
                && empty($url['query']) && empty($url['fragment']) && empty($url['user'])
                && in_array($url['path'] ?? '', ['', '/'], true),
            'Secure session cookies enabled' => (bool) config('session.secure'),
            'Business/legal review gate enabled' => (bool) config('juanovio.legal_reviewed'),
            'Legal page draft headings replaced' => ! $legalDrafts,
            'Inquiry configuration allows submissions' => app(InquiryForm::class)->available(),
            'Non-placeholder sender configured' => filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)
                && ! preg_match('/@(.*\.)?example\.(com|org|net|test)$/i', (string) config('mail.from.address')),
            'Production asset manifest present' => is_file(public_path('build/manifest.json')),
            'Social image dimensions match metadata' => $dimensions && $dimensions[0] === ($image['width'] ?? null) && $dimensions[1] === ($image['height'] ?? null),
        ];

        $this->table(['Configured prerequisite', 'Result'], collect($checks)->map(fn ($passes, $label) => [$label, $passes ? 'PASS' : 'NOT READY'])->values()->all());
        $this->comment('Configuration checks only: no mail was sent. Verify legal copy, domain ownership, credentials, inbox delivery, redirects and public crawling separately.');

        return in_array(false, array_map('boolval', $checks), true) ? self::FAILURE : self::SUCCESS;
    }
}
