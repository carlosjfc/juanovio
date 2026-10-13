@props(['title', 'description', 'page'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-analytics.head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ \App\Support\Locale::url($page) }}">
    @foreach(['en', 'es', 'x-default'] as $alternate)
        <link rel="alternate" hreflang="{{ $alternate }}" href="{{ \App\Support\Locale::url($page, [], $alternate === 'es' ? 'es' : 'en') }}">
    @endforeach
    <link rel="icon" type="image/x-icon" sizes="16x16 32x32 64x64" href="{{ asset('favicon.ico') }}">
    @foreach([16, 32, 64, 128, 256, 512] as $iconSize)
        <link rel="icon" type="image/png" sizes="{{ $iconSize }}x{{ $iconSize }}" href="{{ asset('brand/icons/juanovio-favicon-'.$iconSize.'.png') }}">
    @endforeach
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ \App\Support\Locale::url($page) }}">
    <meta property="og:site_name" content="Juanovio International Trading Inc.">
    <meta property="og:locale" content="{{ app()->getLocale() === 'es' ? 'es_ES' : 'en_US' }}">
    <meta property="og:image" content="{{ asset(config('juanovio.social_image.path')) }}">
    <meta property="og:image:type" content="{{ config('juanovio.social_image.type') }}">
    <meta property="og:image:width" content="{{ config('juanovio.social_image.width') }}">
    <meta property="og:image:height" content="{{ config('juanovio.social_image.height') }}">
    <meta property="og:image:alt" content="{{ __(config('juanovio.social_image.alt')) }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ asset(config('juanovio.social_image.path')) }}">
    <meta name="twitter:image:alt" content="{{ __(config('juanovio.social_image.alt')) }}">
    <script type="application/ld+json">{!! app(\App\Support\PageSchema::class)->toJson($page) !!}</script>
    @if(! config('juanovio.legal_reviewed') || in_array($page, ['privacy', 'terms']))<meta name="robots" content="noindex, nofollow">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <a class="skip-link" href="#main">{{ __("Skip to content") }}</a>
    <header id="page-top" class="site-header wrap" tabindex="-1">
        <a class="brand" href="{{ \App\Support\Locale::url('home') }}" aria-label="{{ __('Juanovio International Trading Inc. — Home') }}">
            <x-brand-image variant="horizontal" :compact="true" />
        </a>
        <flux:navbar class="desktop-nav" :aria-label="__('Primary navigation')">@foreach(config('juanovio.navigation') as $name => $label)<flux:navbar.item href="{{ \App\Support\Locale::url($name) }}" :current="$page === $name" :aria-current="$page === $name ? 'page' : null">{{ $label }}</flux:navbar.item>@endforeach</flux:navbar>
        <flux:dropdown class="language-selector" position="bottom" align="end">
            <flux:button variant="ghost" icon:trailing="chevron-down" :aria-label="__('Choose language')">{{ app()->getLocale() === 'es' ? 'Español' : 'English' }}</flux:button>
            <flux:menu :aria-label="__('Choose language')">
                @foreach(['en' => 'English', 'es' => 'Español'] as $locale => $label)
                    <flux:menu.item href="{{ route('language', ['locale' => $locale, 'page' => $page, 'type' => request()->query('type')]) }}" data-language-choice="{{ $locale }}" lang="{{ $locale }}" :aria-current="app()->getLocale() === $locale ? 'true' : null">{{ $label }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
        <flux:dropdown class="mobile-nav" position="bottom" align="end">
            <flux:button class="mobile-menu-trigger" variant="ghost" icon="bars-3" icon:variant="outline" :aria-label="__('Open navigation menu')" />
            <flux:menu class="mobile-menu" :aria-label="__('Mobile navigation')">
                @foreach(config('juanovio.navigation') as $name => $label)
                    <flux:menu.item href="{{ \App\Support\Locale::url($name) }}" :aria-current="$page === $name ? 'page' : null">{{ $label }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </header>
    <main id="main">{{ $slot }}</main>
    <footer class="site-footer"><div class="wrap footer-grid">
        <div><a class="footer-brand" href="{{ \App\Support\Locale::url('home') }}" aria-label="{{ __('Juanovio International Trading Inc. — Home') }}"><x-brand-image variant="full" :lazy="true" /></a><p>{{ __("Building connections across borders.") }}<br>{{ __("Developing opportunities with care.") }}</p></div>
        <nav class="footer-navigation" aria-label="{{ __('Footer navigation') }}">@foreach(config('juanovio.navigation') as $name => $label)<a href="{{ \App\Support\Locale::url($name) }}">{{ $label }}</a>@endforeach</nav>
        <div><nav class="footer-legal" aria-label="{{ __('Legal navigation') }}"><a href="{{ \App\Support\Locale::url('privacy') }}">{{ __("Privacy policy") }}</a><a href="{{ \App\Support\Locale::url('terms') }}">{{ __("Terms of use") }}</a></nav><p class="footer-copyright">© {{ date('Y') }} Juanovio</p></div>
    </div></footer>
    @if(\App\Support\Analytics::settings())
        <div class="wrap"><flux:button type="button" variant="ghost" data-analytics-preferences>{{ __("Analytics preferences") }}</flux:button></div>
    @endif
    <x-analytics.body />
    <div class="back-to-top" x-data="{ visible: window.scrollY > 400 }" x-cloak x-show="visible" @scroll.window.throttle.100ms="visible = window.scrollY > 400">
        <flux:button variant="primary" icon="arrow-up" icon:variant="outline" :aria-label="__('Back to top')" :title="__('Back to top')" @click="document.getElementById('page-top').focus({ preventScroll: true }); window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' })" />
    </div>
    @livewireScripts
    @fluxScripts
</body>
</html>
