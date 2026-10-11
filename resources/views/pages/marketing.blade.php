<x-layouts.site :title="$content['title']" :description="$content['description']" :page="$page">
    <section class="hero wrap {{ $page === 'home' ? 'hero-home' : '' }}">
        @if($page !== 'home')<nav class="breadcrumb" aria-label="Breadcrumb"><a href="{{ route('home') }}">Home</a><span aria-hidden="true"> / </span><span aria-current="page">{{ config('juanovio.navigation.'.$page, $content['heading']) }}</span></nav>@endif
        <p class="eyebrow">{{ $content['eyebrow'] }}</p><h1>{{ $content['heading'] }}</h1><p class="hero-intro">{{ $content['intro'] }}</p>
        @if($page === 'home')<div class="actions"><flux:button variant="primary" href="{{ route('contact') }}">Discuss a Trade Opportunity</flux:button><a class="text-link" href="{{ route('trade') }}">Explore What We Trade ↗</a></div><div class="hero-note"><span>Supply. Demand. Connection.</span><span>A thoughtful approach to international trade</span></div>@endif
    </section>
    @if($page === 'home')
        <section class="soft-section"><div class="wrap"><p class="eyebrow">Commercial conversations</p><h2>Different needs. Shared opportunity.</h2><div class="cards">
        @foreach(['buy' => ['For Buyers', 'Share your sourcing requirements and explore potential supply options.', 'Submit a Buying Inquiry'], 'supply' => ['For Suppliers', 'Introduce your products, capacity, and target markets.', 'Introduce Your Supply'], 'partnership' => ['For Partners', 'Explore collaboration in sourcing, logistics coordination, and market development.', 'Discuss a Partnership']] as $type => $card)
            <article class="card"><span class="index" aria-hidden="true">0{{ $loop->iteration }}</span><h3>{{ $card[0] }}</h3><p>{{ $card[1] }}</p><a class="text-link" href="{{ route('contact', ['type' => $type]) }}">{{ $card[2] }} ↗</a></article>
        @endforeach</div></div></section>
    @endif
    @if(in_array($page, ['home', 'trade']))
        <section class="wrap section"><div class="section-intro"><p class="eyebrow">What we explore</p><h2>Open to possibility.<br>Grounded in commercial fit.</h2><p>Categories of interest, evaluated inquiry by inquiry.</p></div><div class="category-list">
        @foreach(config('juanovio.categories') as $category)<article><span class="index" aria-hidden="true">0{{ $loop->iteration }}</span><div><h3>{{ $category['title'] }}</h3><p>{{ $category['text'] }}</p></div></article>@endforeach
        </div></section>
    @endif
    @if($page === 'markets')<section class="wrap cards section">@foreach(config('juanovio.markets') as $market)<article class="card"><p class="eyebrow">{{ $market['status'] }}</p><h2>{{ $market['title'] }}</h2><p>{{ $market['text'] }}</p></article>@endforeach</section>@endif
    @if(count($content['sections']))<section class="wrap editorial section {{ count($content['sections']) === 4 ? 'editorial-pairs' : '' }}">@foreach($content['sections'] as $section)<article>@if($page === 'how-we-work')<p class="index" aria-hidden="true">0{{ $loop->iteration }}</p>@endif<h2>{{ $section['title'] }}</h2><p>{{ $section['text'] }}</p></article>@endforeach</section>@endif
    @if($page === 'home')<section class="wrap section"><p class="eyebrow">Our approach</p><h2>A practical path forward.</h2><ol class="approach"><li>Understand the requirement</li><li>Evaluate commercial fit</li><li>Coordinate next steps</li></ol><a class="text-link" href="{{ route('how-we-work') }}">Discover how we work ↗</a></section>@endif
    @if(isset($content['disclosure']))<aside class="wrap disclosure"><p>{{ $content['disclosure'] }}</p></aside>@endif
    @if($page === 'contact')<section class="wrap contact-section"><livewire:inquiry-form /></section>@endif
    @if(!in_array($page, ['contact', 'privacy', 'terms']))<section class="closing"><div class="wrap"><p class="eyebrow">The next conversation</p><h2>Let’s explore what’s possible.</h2><div class="actions"><flux:button variant="primary" href="{{ route('contact', $page === 'trade' ? ['type' => 'buy'] : []) }}">{{ $page === 'trade' ? 'Submit a Buying Inquiry' : 'Discuss a Trade Opportunity' }}</flux:button>@if($page === 'trade')<a class="text-link" href="{{ route('contact', ['type' => 'supply']) }}">Introduce Your Supply ↗</a>@endif</div></div></section>@endif
</x-layouts.site>
