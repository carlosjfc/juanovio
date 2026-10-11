@if(\App\Support\Analytics::settings())
    <section class="analytics-preferences wrap notice" data-analytics-banner hidden aria-labelledby="analytics-heading">
        <h2 id="analytics-heading">Analytics preferences</h2>
        <p>With your permission, we use Google Analytics to understand page visits and successful inquiries. Advertising tracking is disabled. You can change your choice at any time. <a href="{{ route('privacy') }}">Read our privacy policy</a>.</p>
        <div class="actions">
            <flux:button type="button" variant="primary" data-analytics-choice="accept">Allow analytics</flux:button>
            <flux:button type="button" data-analytics-choice="reject">Reject analytics</flux:button>
        </div>
        <p data-analytics-status role="status" aria-live="polite"></p>
    </section>
@endif
