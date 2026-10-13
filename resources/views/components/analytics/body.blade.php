@if(\App\Support\Analytics::settings())
    <section class="analytics-preferences wrap notice" data-analytics-banner hidden aria-labelledby="analytics-heading">
        <h2 id="analytics-heading">{{ __("Analytics preferences") }}</h2>
        <p>{{ __("With your permission, we use Google Analytics to understand page visits and successful inquiries. Advertising tracking is disabled. You can change your choice at any time. ") }}<a href="{{ \App\Support\Locale::url('privacy') }}">{{ __("Read our privacy policy") }}</a>.</p>
        <div class="actions">
            <flux:button type="button" variant="primary" data-analytics-choice="accept">{{ __("Allow analytics") }}</flux:button>
            <flux:button type="button" data-analytics-choice="reject">{{ __("Reject analytics") }}</flux:button>
        </div>
        <p data-analytics-status role="status" aria-live="polite" data-saved="{{ __('Your analytics preference has been saved.') }}" data-unavailable="{{ __('Your choice applies to this page. Browser storage is unavailable; preferences cannot be saved.') }}"></p>
    </section>
@endif
