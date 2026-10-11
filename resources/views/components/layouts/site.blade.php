@props(['title', 'description', 'page'])
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-analytics.head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="canonical" href="{{ route($page) }}">
    <link rel="icon" type="image/x-icon" sizes="16x16 32x32 64x64" href="{{ asset('favicon.ico') }}">
    @foreach([16, 32, 64, 128, 256, 512] as $iconSize)
        <link rel="icon" type="image/png" sizes="{{ $iconSize }}x{{ $iconSize }}" href="{{ asset('brand/icons/juanovio-favicon-'.$iconSize.'.png') }}">
    @endforeach
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $title }}">
    <meta property="og:description" content="{{ $description }}">
    <meta property="og:url" content="{{ route($page) }}">
    <meta property="og:site_name" content="Juanovio International Trading Inc.">
    <meta property="og:locale" content="{{ str_replace('-', '_', app()->getLocale()) === 'en' ? 'en_US' : str_replace('-', '_', app()->getLocale()) }}">
    <meta property="og:image" content="{{ asset(config('juanovio.social_image.path')) }}">
    <meta property="og:image:type" content="{{ config('juanovio.social_image.type') }}">
    <meta property="og:image:width" content="{{ config('juanovio.social_image.width') }}">
    <meta property="og:image:height" content="{{ config('juanovio.social_image.height') }}">
    <meta property="og:image:alt" content="{{ config('juanovio.social_image.alt') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title }}">
    <meta name="twitter:description" content="{{ $description }}">
    <meta name="twitter:image" content="{{ asset(config('juanovio.social_image.path')) }}">
    <meta name="twitter:image:alt" content="{{ config('juanovio.social_image.alt') }}">
    <script type="application/ld+json">{!! app(\App\Support\PageSchema::class)->toJson($page) !!}</script>
    @if(! config('juanovio.legal_reviewed') || in_array($page, ['privacy', 'terms']))<meta name="robots" content="noindex, nofollow">@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body>
    <a class="skip-link" href="#main">Skip to content</a>
    <header id="page-top" class="site-header wrap" tabindex="-1">
        <a class="brand" href="{{ route('home') }}" aria-label="Juanovio International Trading Inc. — Home">
            <x-brand-image variant="horizontal" :compact="true" />
        </a>
        <flux:navbar class="desktop-nav" aria-label="Primary navigation">@foreach(config('juanovio.navigation') as $name => $label)<flux:navbar.item href="{{ route($name) }}" :current="$page === $name" :aria-current="$page === $name ? 'page' : null">{{ $label }}</flux:navbar.item>@endforeach</flux:navbar>
        <flux:dropdown class="mobile-nav" position="bottom" align="end">
            <flux:button class="mobile-menu-trigger" variant="ghost" icon="bars-3" icon:variant="outline" aria-label="Open navigation menu" />
            <flux:menu class="mobile-menu" aria-label="Mobile navigation">
                @foreach(config('juanovio.navigation') as $name => $label)
                    <flux:menu.item href="{{ route($name) }}" :aria-current="$page === $name ? 'page' : null">{{ $label }}</flux:menu.item>
                @endforeach
            </flux:menu>
        </flux:dropdown>
    </header>
    <main id="main">{{ $slot }}</main>
    <footer class="site-footer"><div class="wrap footer-grid">
        <div><a class="footer-brand" href="{{ route('home') }}" aria-label="Juanovio International Trading Inc. — Home"><x-brand-image variant="full" :lazy="true" /></a><p>Building connections across borders.<br>Developing opportunities with care.</p></div>
        <nav class="footer-navigation" aria-label="Footer navigation">@foreach(config('juanovio.navigation') as $name => $label)<a href="{{ route($name) }}">{{ $label }}</a>@endforeach</nav>
        <div><nav class="footer-legal" aria-label="Legal navigation"><a href="{{ route('privacy') }}">Privacy policy · Draft</a><a href="{{ route('terms') }}">Terms of use · Draft</a></nav><p class="footer-copyright">© {{ date('Y') }} Juanovio</p></div>
    </div></footer>
    @if(\App\Support\Analytics::settings())
        <div class="wrap"><flux:button type="button" variant="ghost" data-analytics-preferences>Analytics preferences</flux:button></div>
    @endif
    <x-analytics.body />
    <div class="back-to-top" x-data="{ visible: window.scrollY > 400 }" x-cloak x-show="visible" @scroll.window.throttle.100ms="visible = window.scrollY > 400">
        <flux:button variant="primary" icon="arrow-up" icon:variant="outline" aria-label="Back to top" title="Back to top" @click="document.getElementById('page-top').focus({ preventScroll: true }); window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' })" />
    </div>
    @livewireScripts
    @fluxScripts
</body>
</html>
