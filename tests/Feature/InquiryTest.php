<?php

namespace Tests\Feature;

use App\Livewire\InquiryForm;
use App\Notifications\TradeInquiry;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
        config(['juanovio.inquiries_enabled' => true, 'juanovio.legal_reviewed' => true, 'juanovio.recipient' => 'inquiries@example.test', 'mail.default' => 'smtp']);
    }

    private function filled()
    {
        return Livewire::test(InquiryForm::class)->set('type', 'buy')
            ->set('name', 'Test Buyer')->set('company', 'Test Company')
            ->set('email', 'buyer@example.test')->set('country', 'Guyana')
            ->set('product', 'Construction materials')
            ->set('message', 'Please discuss specifications and available sourcing options.')
            ->set('consent', true);
    }

    public function test_valid_inquiry_sends_once_and_clears_personal_data(): void
    {
        $component = $this->filled();
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors()->assertSet('sent', true)->assertSet('email', '')->call('submit');
        Notification::assertSentOnDemand(TradeInquiry::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'inquiries@example.test' && $notification->inquiry['product'] === 'Construction materials');
        Notification::assertCount(1);
    }

    public function test_validation_requires_b2b_details_and_consent(): void
    {
        $component = Livewire::test(InquiryForm::class)->set('type', 'supply')->set('email', 'invalid');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors(['name', 'company', 'email', 'country', 'product', 'message', 'consent']);
        Notification::assertNothingSent();
    }

    public function test_general_inquiry_does_not_require_product(): void
    {
        $component = $this->filled()->set('type', 'general')->set('product', '');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors();
    }

    public function test_honeypot_and_fast_submissions_are_rejected(): void
    {
        $this->filled()->call('submit')->assertHasErrors('submission')->assertSet('sent', false);
        $component = $this->filled()->set('website', 'spam');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors('submission');
        Notification::assertNothingSent();
    }

    public function test_attempts_are_limited_even_when_invalid(): void
    {
        $component = Livewire::test(InquiryForm::class);
        $this->travel(4)->seconds();
        for ($i = 0; $i < 5; $i++) {
            $component->call('submit');
        }
        $component->call('submit')->assertHasErrors('submission')->assertSee('Too many attempts');
        Notification::assertNothingSent();
    }

    public function test_disabled_or_unreviewed_or_log_mail_configuration_cannot_send(): void
    {
        foreach (['juanovio.inquiries_enabled' => false, 'juanovio.legal_reviewed' => false, 'mail.default' => 'log'] as $key => $value) {
            $old = config($key);
            config([$key => $value]);
            $this->filled()->call('submit')->assertHasErrors('submission')->assertSet('sent', false);
            config([$key => $old]);
        }
        Notification::assertNothingSent();
    }

    public function test_transport_failure_does_not_report_success(): void
    {
        Notification::shouldReceive('send')->once()->andThrow(new \RuntimeException('Transport failed'));
        $component = $this->filled();
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors('submission')->assertSet('sent', false)->assertSet('email', 'buyer@example.test');
    }

    public function test_email_content_is_escaped(): void
    {
        $this->assertStringNotContainsString('<script>', view('mail.inquiry', ['inquiry' => ['message' => '<script>alert(1)</script>']])->render());
    }
}
