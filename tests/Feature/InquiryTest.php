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
            ->set('category', 'construction')
            ->set('product', 'Construction materials')
            ->set('message', 'Please discuss specifications and available sourcing options.')
            ->set('consent', true);
    }

    public function test_valid_inquiry_sends_once_and_clears_personal_data(): void
    {
        $component = $this->filled();
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors()->assertSet('sent', true)->assertSet('email', '')
            ->assertDispatched('inquiry-submitted', fn ($event, $params) => $params === ['inquiry_type' => 'buy']);
        $component->call('submit')->assertNotDispatched('inquiry-submitted');
        Notification::assertSentOnDemand(TradeInquiry::class, fn ($notification, $channels, $notifiable) => $notifiable->routes['mail'] === 'inquiries@example.test' && $notification->inquiry['product'] === 'Construction materials' && $notification->inquiry['category'] === 'construction');
        $component->assertSet('category', '');
        Notification::assertCount(1);
    }

    public function test_validation_requires_b2b_details_and_consent(): void
    {
        $component = Livewire::test(InquiryForm::class)->set('type', 'supply')->set('email', 'invalid');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors(['name', 'company', 'email', 'country', 'category', 'product', 'message', 'consent']);
        $component->assertNotDispatched('inquiry-submitted');
        Notification::assertNothingSent();
    }

    public function test_general_inquiry_does_not_require_product(): void
    {
        foreach (['general', 'partnership'] as $type) {
            $component = $this->filled()->set('type', $type)->set('product', '')->set('category', '');
            $this->travel(4)->seconds();
            $component->call('submit')->assertHasNoErrors();
        }
    }

    public function test_category_rejects_unknown_values_and_accepts_unsure(): void
    {
        $component = $this->filled()->set('category', 'unlisted');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors('category');
        Notification::assertNothingSent();
        $component->set('category', 'unsure')->call('submit')->assertHasNoErrors()->assertSet('sent', true);
    }

    public function test_category_is_rendered_as_a_readable_label_in_email(): void
    {
        $html = view('mail.inquiry', ['inquiry' => ['category' => 'agriculture-food']])->render();
        $this->assertStringContainsString('Agriculture &amp; Food Commodities', $html);
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
        $component->assertNotDispatched('inquiry-submitted');
    }

    public function test_email_content_is_escaped(): void
    {
        $this->assertStringNotContainsString('<script>', view('mail.inquiry', ['inquiry' => ['message' => '<script>alert(1)</script>']])->render());
    }

    public function test_every_submitted_business_field_and_acknowledgment_reaches_the_rendered_email(): void
    {
        $fields = [
            'type' => 'supply', 'name' => 'Complete Supplier', 'company' => 'Complete Company',
            'email' => 'supplier@example.test', 'country' => 'Supplier country',
            'category' => 'agriculture-food', 'product' => 'Wheat flour specification',
            'quantity' => '64', 'quantityUnit' => 'bags', 'packDetails' => '42.5 kg per bag',
            'origin' => 'Origin port', 'destination' => 'Destination warehouse',
            'message' => "Please review this complete supply inquiry.\nDelivery timing is flexible.", 'consent' => true,
        ];
        $component = Livewire::test(InquiryForm::class);
        foreach ($fields as $field => $value) {
            $component->set($field, $value);
        }
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors()->assertSet('sent', true);
        Notification::assertSentOnDemand(TradeInquiry::class, function ($notification, $channels, $notifiable) use ($fields) {
            $this->assertSame($fields, $notification->inquiry);
            $mail = $notification->toMail($notifiable);
            $this->assertSame('Juanovio — Supplier introduction', $mail->subject);
            $this->assertSame('supplier@example.test', $mail->replyTo[0][0]);
            $html = view($mail->view, $mail->viewData)->render();
            foreach ($fields as $field => $value) {
                $expected = match ($field) {
                    'type' => 'Supplier introduction', 'category' => 'Agriculture & Food Commodities',
                    'quantityUnit' => 'Bags', 'consent' => 'Acknowledged: use of submitted information to review and respond to this inquiry.',
                    default => $value,
                };
                $this->assertStringContainsString(e($expected), $html);
            }
            $this->assertStringContainsString('Privacy acknowledgment', $html);
            $this->assertArrayNotHasKey('website', $notification->inquiry);

            return true;
        });
    }

    public function test_locations_can_be_supplied_independently_or_together(): void
    {
        foreach ([['Origin port', ''], ['', 'Destination city'], ['Origin port', 'Destination city']] as [$origin, $destination]) {
            $component = $this->filled()->set('origin', $origin)->set('destination', $destination);
            $this->travel(4)->seconds();
            $component->call('submit')->assertHasNoErrors()->assertSet('sent', true)->assertSet('origin', '')->assertSet('destination', '');
            Notification::assertSentOnDemand(TradeInquiry::class, fn ($notification) => $notification->inquiry['origin'] === $origin
                && $notification->inquiry['destination'] === $destination && ! array_key_exists('route', $notification->inquiry));
        }
    }

    public function test_location_lengths_are_bounded_and_email_labels_are_distinct(): void
    {
        $component = $this->filled()->set('origin', str_repeat('a', 201))->set('destination', str_repeat('b', 201));
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors(['origin', 'destination']);
        Notification::assertNothingSent();
        $html = view('mail.inquiry', ['inquiry' => ['origin' => 'Origin port', 'destination' => 'Destination city']])->render();
        $this->assertStringContainsString('Origin / sourcing location', $html);
        $this->assertStringContainsString('Destination / delivery location', $html);
    }

    public function test_quantity_requires_a_positive_number_and_matching_unit(): void
    {
        $component = $this->filled()->set('quantity', '64');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors('quantityUnit');
        $component->set('quantityUnit', 'bags')->set('quantity', '0')->call('submit')->assertHasErrors('quantity');
        $component->set('quantity', '-2')->call('submit')->assertHasErrors('quantity');
        $component->set('quantity', 'many')->call('submit')->assertHasErrors('quantity');
        Notification::assertNothingSent();
    }

    public function test_quantity_unit_requires_quantity_and_rejects_unknown_units(): void
    {
        $component = $this->filled()->set('quantityUnit', 'mt');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasErrors('quantity');
        $component->set('quantity', '235')->set('quantityUnit', 'tons')->call('submit')->assertHasErrors('quantityUnit');
        Notification::assertNothingSent();
    }

    public function test_bag_quantity_and_pack_details_are_sent_and_cleared(): void
    {
        $component = $this->filled()->set('quantity', '64')->set('quantityUnit', 'bags')->set('packDetails', '42.5 kg per bag');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors()->assertSet('quantity', '')->assertSet('quantityUnit', '')->assertSet('packDetails', '');
        Notification::assertSentOnDemand(TradeInquiry::class, fn ($notification) => $notification->inquiry['quantity'] === '64'
            && $notification->inquiry['quantityUnit'] === 'bags' && $notification->inquiry['packDetails'] === '42.5 kg per bag');
    }

    public function test_switching_unit_clears_and_excludes_irrelevant_pack_details(): void
    {
        $component = $this->filled()->set('quantity', '235.5')->set('quantityUnit', 'bags')->set('packDetails', '42.5 kg per bag');
        $component->set('quantityUnit', 'mt')->assertSet('packDetails', '')->set('packDetails', 'stale details');
        $this->travel(4)->seconds();
        $component->call('submit')->assertHasNoErrors();
        Notification::assertSentOnDemand(TradeInquiry::class, fn ($notification) => ! array_key_exists('packDetails', $notification->inquiry));
        $html = view('mail.inquiry', ['inquiry' => ['quantityUnit' => 'mt']])->render();
        $this->assertStringContainsString('Metric tonnes (MT)', $html);
    }
}
