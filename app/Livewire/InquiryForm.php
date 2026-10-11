<?php

namespace App\Livewire;

use App\Notifications\TradeInquiry;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Throwable;

class InquiryForm extends Component
{
    public string $type = 'general';

    public string $name = '';

    public string $company = '';

    public string $email = '';

    public string $country = '';

    public string $product = '';

    public string $category = '';

    public string $quantity = '';

    public string $quantityUnit = '';

    public string $packDetails = '';

    public string $origin = '';

    public string $destination = '';

    public string $message = '';

    public string $website = '';

    public bool $consent = false;

    #[Locked]
    public int $startedAt;

    #[Locked]
    public bool $sent = false;

    public function mount(): void
    {
        $type = request()->query('type');
        $this->type = is_string($type) && array_key_exists($type, config('juanovio.types')) ? $type : 'general';
        $this->startedAt = now()->timestamp;
    }

    public function available(): bool
    {
        return config('juanovio.inquiries_enabled') && config('juanovio.legal_reviewed')
            && filter_var(config('juanovio.recipient'), FILTER_VALIDATE_EMAIL)
            && in_array(config('mail.mailers.'.config('mail.default').'.transport'), ['smtp', 'sendmail', 'ses', 'postmark', 'resend'], true);
    }

    public function updatedQuantityUnit(): void
    {
        if (! in_array($this->quantityUnit, ['bags', 'other'], true)) {
            $this->packDetails = '';
        }
    }

    public function submit(): void
    {
        if ($this->sent) {
            return;
        }
        if (! $this->available()) {
            $this->addError('submission', 'The inquiry form is not accepting submissions yet. Please return later.');

            return;
        }

        $key = 'inquiry:'.hash('sha256', request()->ip().'|'.config('app.key'));
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('submission', 'Too many attempts. Please try again in a few minutes.');

            return;
        }
        RateLimiter::hit($key, 600);
        if ($this->website !== '' || now()->timestamp - $this->startedAt < 3) {
            $this->addError('submission', 'Your inquiry could not be submitted. Please wait a moment and try again.');

            return;
        }

        $data = $this->validate([
            'type' => ['required', Rule::in(array_keys(config('juanovio.types')))],
            'name' => ['required', 'string', 'max:120'],
            'company' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'country' => ['required', 'string', 'max:100'],
            'category' => ['required_if:type,buy,supply', 'string', Rule::in([...array_keys(config('juanovio.categories')), 'unsure'])],
            'product' => ['required_if:type,buy,supply', 'string', 'max:200'],
            'quantity' => ['nullable', 'required_with:quantityUnit', 'numeric', 'gt:0', 'max:999999999999'],
            'quantityUnit' => ['required_with:quantity', 'string', Rule::in(array_keys(config('juanovio.quantity_units')))],
            'packDetails' => ['exclude_unless:quantityUnit,bags,other', 'nullable', 'string', 'max:200'],
            'origin' => ['nullable', 'string', 'max:200'],
            'destination' => ['nullable', 'string', 'max:200'],
            'message' => ['required', 'string', 'min:20', 'max:5000'],
            'consent' => ['accepted'],
        ]);
        try {
            // Send synchronously: success means the transport accepted the message.
            // Do not persist inquiry content or place it in diagnostic logs.
            Notification::route('mail', config('juanovio.recipient'))->notify(new TradeInquiry($data));
        } catch (Throwable $exception) {
            Log::warning('Inquiry transport failed.', ['exception_type' => get_class($exception)]);
            $this->addError('submission', 'Your inquiry could not be sent. Please try again later.');

            return;
        }
        $this->reset(['name', 'company', 'email', 'country', 'category', 'product', 'quantity', 'quantityUnit', 'packDetails', 'origin', 'destination', 'message', 'consent']);
        $this->sent = true;
        $this->dispatch('inquiry-submitted', inquiry_type: $data['type']);
    }

    public function render()
    {
        return view('livewire.inquiry-form');
    }
}
