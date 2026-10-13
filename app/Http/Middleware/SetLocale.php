<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $spanish = $request->is('es', 'es/*');
        $locale = $spanish ? 'es' : 'en';
        if ($request->is('livewire*')) {
            $locale = $request->cookie('juanovio_language') === 'es' ? 'es' : 'en';
        }
        app()->setLocale($locale);

        // Only a deliberate saved choice affects routing; never infer language from location.
        if ($request->isMethod('GET') && ! $spanish && $request->cookie('juanovio_language') === 'es'
            && in_array($request->route()?->getName(), array_keys(config('website')), true)) {
            return redirect(Locale::url($request->route()->getName(), $request->query(), 'es'));
        }

        if ($request->route()?->getName() !== 'language' && in_array($request->cookie('juanovio_language'), ['en', 'es'], true)) {
            Cookie::queue(cookie('juanovio_language', $request->cookie('juanovio_language'), 60 * 24 * 365,
                '/', null, $request->isSecure(), true, false, 'lax'));
        }

        self::translateContent();

        return $next($request);
    }

    public static function translateContent(): void
    {
        $translate = function (array $values) use (&$translate): array {
            return array_map(fn ($value) => is_array($value) ? $translate($value) : (is_string($value) ? __($value) : $value), $values);
        };
        config(['website' => $translate(require config_path('website.php'))]);
        $brand = require config_path('juanovio.php');
        foreach (['navigation', 'types', 'quantity_units', 'categories', 'markets'] as $key) {
            config(['juanovio.'.$key => $translate($brand[$key])]);
        }
    }
}
