<?php

namespace App\Support;

class Locale
{
    public static function url(string $page, array $parameters = [], ?string $locale = null): string
    {
        return route(($locale ?? app()->getLocale()) === 'es' ? 'es.'.$page : $page, $parameters);
    }
}
