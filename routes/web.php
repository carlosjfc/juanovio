<?php

use Illuminate\Support\Facades\Route;

foreach (array_keys(config('website')) as $page) {
    Route::get($page === 'home' ? '/' : '/'.$page, fn () => view('pages.marketing', [
        'page' => $page,
        'content' => config('website.'.$page),
    ]))->name($page);
}

Route::get('/sitemap.xml', fn () => response()->view('sitemap', [], 200)->header('Content-Type', 'application/xml'))->name('sitemap');

Route::get('/robots.txt', fn () => response(
    config('juanovio.legal_reviewed')
        ? "User-agent: *\nDisallow:\nSitemap: ".route('sitemap')."\n"
        : "User-agent: *\nDisallow: /\n"
)->header('Content-Type', 'text/plain'));
