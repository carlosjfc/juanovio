<?php

use App\Support\Locale;
use Illuminate\Support\Facades\Route;

foreach (array_keys(config('website')) as $page) {
    Route::get($page === 'home' ? '/' : '/'.$page, fn () => view('pages.marketing', [
        'page' => $page,
        'content' => config('website.'.$page),
    ]))->name($page);
    Route::get($page === 'home' ? '/es' : '/es/'.$page, fn () => view('pages.marketing', [
        'page' => $page,
        'content' => config('website.'.$page),
    ]))->name('es.'.$page);
}

Route::get('/language/{locale}', function (string $locale) {
    abort_unless(in_array($locale, ['en', 'es'], true), 404);
    $page = request()->query('page', 'home');
    abort_unless(is_string($page) && array_key_exists($page, config('website')), 404);
    $type = request()->query('type');
    $parameters = is_string($type) && array_key_exists($type, config('juanovio.types')) ? ['type' => $type] : [];

    return redirect(Locale::url($page, $parameters, $locale))->withCookie(
        cookie('juanovio_language', $locale, 60 * 24 * 365, '/', null, request()->isSecure(), true, false, 'lax')
    );
})->name('language');

Route::get('/sitemap.xml', fn () => response()->view('sitemap', [], 200)->header('Content-Type', 'application/xml'))->name('sitemap');

Route::get('/robots.txt', fn () => response(
    config('juanovio.legal_reviewed')
        ? "User-agent: *\nDisallow:\nSitemap: ".route('sitemap')."\n"
        : "User-agent: *\nDisallow: /\n"
)->header('Content-Type', 'text/plain'));
