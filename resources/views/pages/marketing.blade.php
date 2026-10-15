<x-layouts.site :title="$content['title']" :description="$content['description']" :page="$page">
    <section class="hero wrap {{ $page === 'home' ? 'hero-home' : '' }}">
        @if($page !== 'home')<nav class="breadcrumb" aria-label="{{ __('Breadcrumb') }}"><a href="{{ \App\Support\Locale::url('home') }}">{{ __("Home") }}</a><span aria-hidden="true"> / </span><span aria-current="page">{{ config('juanovio.navigation.'.$page, $content['heading']) }}</span></nav>@endif
        <p class="eyebrow">{{ $content['eyebrow'] }}</p><h1>{{ $content['heading'] }}</h1><p class="hero-intro">{{ $content['intro'] }}</p>
        @if(isset($content['help']))<p class="hero-intro">{{ $content['help'] }}</p>@endif
        @if($page === 'home')<div class="actions"><flux:button variant="primary" href="{{ \App\Support\Locale::url('contact') }}">{{ __("Discuss a Trade Opportunity") }}</flux:button><x-cta-link href="{{ \App\Support\Locale::url('trade') }}">{{ __("Explore What We Trade") }}</x-cta-link></div><div class="hero-note"><span>{{ __("Supply. Demand. Connection.") }}</span><span>{{ __("A thoughtful approach to international trade") }}</span></div>@endif
    </section>
    @if($page === 'home')
        <section class="soft-section"><div class="wrap"><p class="eyebrow">{{ __("Commercial conversations") }}</p><h2>{{ __("Different needs. Shared opportunity.") }}</h2><div class="cards">
        @foreach(['buy' => ['For Buyers', 'Share your sourcing requirements and explore potential supply options.', 'Submit a Buying Inquiry'], 'supply' => ['For Suppliers', 'Introduce your products, capacity, and target markets.', 'Introduce Your Supply'], 'partnership' => ['For Partners', 'Explore collaboration in sourcing, logistics coordination, and market development.', 'Discuss a Partnership']] as $type => $card)
            <article class="card"><span class="index" aria-hidden="true">0{{ $loop->iteration }}</span><h3>{{ __($card[0]) }}</h3><p>{{ __($card[1]) }}</p><x-cta-link href="{{ \App\Support\Locale::url('contact', ['type' => $type]) }}">{{ __($card[2]) }}</x-cta-link></article>
        @endforeach</div></div></section>
    @endif
    @if(in_array($page, ['home', 'trade']))
        <section class="wrap section"><div class="section-intro"><p class="eyebrow">{{ __("What we explore") }}</p><h2>{{ __("Open to possibility.") }}<br>{{ __("Grounded in commercial fit.") }}</h2><p>{{ __("Categories of interest, evaluated inquiry by inquiry.") }}</p></div><div class="category-list">
        @foreach(config('juanovio.categories') as $category)<article><span class="index" aria-hidden="true">0{{ $loop->iteration }}</span><div><h3>{{ $category['title'] }}</h3><p>{{ $category['text'] }}</p></div></article>@endforeach
        </div></section>
    @endif
    @if($page === 'markets')
        <section class="wrap cards section">
            @foreach(config('juanovio.markets') as $key => $market)
                <article class="card market-panel" aria-labelledby="market-{{ $key }}">
                    <h2 id="market-{{ $key }}">{{ $market['title'] }}</h2>
                    <p>{{ $market['text'] }}</p>
                    <ul class="market-countries" data-market="{{ $key }}">
                        @foreach($market['countries'] as $code => $country)<li data-country="{{ $code }}">{{ $country }}</li>@endforeach
                    </ul>
                </article>
            @endforeach
        </section>
    @endif
    @if(count($content['sections']))<section class="wrap editorial section {{ count($content['sections']) === 4 ? 'editorial-pairs' : '' }}">@foreach($content['sections'] as $section)<article>@if($page === 'how-we-work')<p class="index" aria-hidden="true">0{{ $loop->iteration }}</p>@endif<h2>{{ $section['title'] }}</h2><p><x-policy-text :text="$section['text']" /></p>@if(isset($section['cta']))<x-cta-link href="{{ \App\Support\Locale::url('markets') }}">{{ $section['cta'] }}</x-cta-link>@endif</article>@endforeach</section>@endif
    @if($page === 'home')<section class="wrap section"><p class="eyebrow">{{ __("Our approach") }}</p><h2>{{ __("A practical path forward.") }}</h2><ol class="approach"><li>{{ __("Understand the requirement") }}</li><li>{{ __("Evaluate commercial fit") }}</li><li>{{ __("Coordinate next steps") }}</li></ol><x-cta-link href="{{ \App\Support\Locale::url('how-we-work') }}">{{ __("Discover how we work") }}</x-cta-link></section>@endif
    @if(isset($content['disclosure']))<aside class="wrap disclosure"><p>{{ $content['disclosure'] }}</p></aside>@endif
    @if($page === 'contact')<section class="wrap contact-section"><livewire:inquiry-form /></section>@endif
    @if(!in_array($page, ['contact', 'privacy', 'terms']))<section class="closing"><div class="wrap"><p class="eyebrow">{{ __("The next conversation") }}</p><h2>{{ __("Let’s explore what’s possible.") }}</h2><div class="actions"><flux:button variant="primary" href="{{ \App\Support\Locale::url('contact', in_array($page, ['trade', 'markets']) ? ['type' => 'buy'] : []) }}">{{ __(match($page) { 'markets' => "I'm a Buyer or Importer", 'trade' => 'Submit a Buying Inquiry', default => 'Discuss a Trade Opportunity' }) }}</flux:button>@if(in_array($page, ['trade', 'markets']))<x-cta-link href="{{ \App\Support\Locale::url('contact', ['type' => 'supply']) }}">{{ __($page === 'markets' ? "I'm a Producer or Supplier" : 'Introduce Your Supply') }}</x-cta-link>@endif</div></div></section>@endif
</x-layouts.site>
