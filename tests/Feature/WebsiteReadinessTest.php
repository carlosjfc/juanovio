<?php

namespace Tests\Feature;

use Tests\TestCase;

class WebsiteReadinessTest extends TestCase
{
    public function test_missing_social_configuration_reports_not_ready_without_crashing(): void
    {
        config(['juanovio.social_image' => null]);
        $this->artisan('website:check')->expectsOutputToContain('Social image dimensions match metadata')->assertExitCode(1);
    }

    public function test_prelaunch_configuration_fails_without_exposing_secrets(): void
    {
        config(['app.debug' => true, 'juanovio.legal_reviewed' => false, 'mail.password' => 'private-test-secret']);
        $this->artisan('website:check')->expectsOutputToContain('NOT READY')
            ->expectsOutputToContain('Configuration checks only')->assertExitCode(1);
    }

    public function test_configured_prerequisites_pass_but_do_not_claim_delivery_verification(): void
    {
        $this->app['env'] = 'production';
        config([
            'app.debug' => false, 'app.url' => 'https://trade-company.com', 'app.key' => 'configured-test-key',
            'session.secure' => true, 'juanovio.legal_reviewed' => true, 'juanovio.inquiries_enabled' => true,
            'juanovio.recipient' => 'inquiries@trade-company.com', 'mail.default' => 'smtp',
            'mail.from.address' => 'website@trade-company.com',
            'website.privacy.heading' => 'Privacy Policy', 'website.terms.heading' => 'Terms of Use',
        ]);
        $this->artisan('website:check')->expectsOutputToContain('Verify legal copy')->assertExitCode(0);
    }
}
