<?php

namespace App\Support;

class PageSchema
{
    public function forPage(string $page): array
    {
        $content = config('website.'.$page);
        $home = Locale::url('home');
        $url = Locale::url($page);
        $organizationId = $home.'#organization';
        $websiteId = $home.'#website';
        $label = config('juanovio.navigation.'.$page, $content['heading']);

        $graph = [
            [
                '@type' => 'Organization',
                '@id' => $organizationId,
                'name' => 'Juanovio International Trading Inc.',
                'url' => $home,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => asset(config('juanovio.brand_assets.full.path')),
                    'width' => config('juanovio.brand_assets.full.width'),
                    'height' => config('juanovio.brand_assets.full.height'),
                ],
            ],
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'url' => $home,
                'name' => 'Juanovio International Trading Inc.',
                'alternateName' => 'JUANOVIO',
                'publisher' => ['@id' => $organizationId],
                'inLanguage' => str_replace('_', '-', app()->getLocale()),
            ],
        ];

        $webpage = [
            '@type' => match ($page) {
                'about' => 'AboutPage',
                'contact' => 'ContactPage',
                'trade', 'markets' => 'CollectionPage',
                default => 'WebPage',
            },
            '@id' => $url.'#webpage',
            'url' => $url,
            'name' => $content['title'],
            'description' => $content['description'],
            'isPartOf' => ['@id' => $websiteId],
            'publisher' => ['@id' => $organizationId],
            'inLanguage' => str_replace('_', '-', app()->getLocale()),
        ];

        if (in_array($page, ['about', 'contact'], true)) {
            $webpage['about'] = ['@id' => $organizationId];
        }

        if ($page !== 'home') {
            $webpage['breadcrumb'] = ['@id' => $url.'#breadcrumb'];
            $graph[] = [
                '@type' => 'BreadcrumbList',
                '@id' => $url.'#breadcrumb',
                'itemListElement' => [
                    ['@type' => 'ListItem', 'position' => 1, 'name' => __('Home'), 'item' => $home],
                    ['@type' => 'ListItem', 'position' => 2, 'name' => $label, 'item' => $url],
                ],
            ];
        }

        if (in_array($page, ['trade', 'markets'], true)) {
            $items = config($page === 'trade' ? 'juanovio.categories' : 'juanovio.markets');
            $webpage['mainEntity'] = ['@id' => $url.'#opportunities'];
            $graph[] = [
                '@type' => 'ItemList',
                '@id' => $url.'#opportunities',
                'name' => $content['eyebrow'],
                'numberOfItems' => count($items),
                'itemListElement' => array_map(fn (array $item, int $position) => [
                    '@type' => 'ListItem',
                    'position' => $position + 1,
                    'item' => [
                        '@type' => 'Thing',
                        'name' => $item['title'],
                        'description' => isset($item['status']) ? $item['status'].'. '.$item['text'] : $item['text'],
                    ],
                ], array_values($items), array_keys(array_values($items))),
            ];
        }

        $graph[] = $webpage;

        return ['@context' => 'https://schema.org', '@graph' => $graph];
    }

    public function toJson(string $page): string
    {
        return json_encode($this->forPage($page), JSON_THROW_ON_ERROR | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
