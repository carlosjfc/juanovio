<?php

namespace Tests\Feature;

use App\Support\PageSchema;
use Tests\TestCase;

class PageSchemaTest extends TestCase
{
    public function test_every_page_renders_a_connected_schema_matching_its_canonical_content(): void
    {
        foreach (config('website') as $page => $content) {
            $html = $this->get(route($page))->assertOk()->getContent();
            $this->assertSame(1, preg_match_all('~<script type="application/ld\+json">(.*?)</script>~s', $html, $matches));
            $schema = json_decode($matches[1][0], true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame('https://schema.org', $schema['@context']);
            $nodes = array_column($schema['@graph'], null, '@id');
            $webpage = $nodes[route($page).'#webpage'];
            $this->assertSame($content['title'], $webpage['name']);
            $this->assertSame($content['description'], $webpage['description']);
            $this->assertSame(route($page), $webpage['url']);
            $this->assertArrayHasKey($webpage['isPartOf']['@id'], $nodes);
            $this->assertArrayHasKey($webpage['publisher']['@id'], $nodes);
            $this->assertSame(match ($page) {
                'about' => 'AboutPage', 'contact' => 'ContactPage',
                'trade', 'markets' => 'CollectionPage', default => 'WebPage',
            }, $webpage['@type']);
            if ($page === 'home') {
                $this->assertArrayNotHasKey('breadcrumb', $webpage);
            } else {
                $breadcrumb = $nodes[$webpage['breadcrumb']['@id']]['itemListElement'];
                $this->assertSame([1, 2], array_column($breadcrumb, 'position'));
                $this->assertSame(route('home'), $breadcrumb[0]['item']);
                $this->assertSame(route($page), $breadcrumb[1]['item']);
                $this->assertStringContainsString(e($breadcrumb[1]['name']), $html);
            }
            $organization = $nodes[route('home').'#organization'];
            $this->assertSame(['@type', '@id', 'name', 'url', 'logo'], array_keys($organization));
            $this->assertFileExists(public_path(config('juanovio.brand_assets.full.path')));
        }
    }

    public function test_opportunity_lists_match_visible_copy_without_claiming_products_or_market_coverage(): void
    {
        foreach (['trade' => 'categories', 'markets' => 'markets'] as $page => $key) {
            $schema = app(PageSchema::class)->forPage($page);
            $nodes = array_column($schema['@graph'], null, '@id');
            $list = $nodes[route($page).'#opportunities'];
            $items = array_values(config('juanovio.'.$key));
            $this->assertCount(count($items), $list['itemListElement']);
            foreach ($list['itemListElement'] as $index => $entry) {
                $this->assertSame($items[$index]['title'], $entry['item']['name']);
                $this->assertStringContainsString($items[$index]['text'], $entry['item']['description']);
                $this->assertSame('Thing', $entry['item']['@type']);
            }
            $json = app(PageSchema::class)->toJson($page);
            foreach (['offers', 'areaServed', 'aggregateRating', 'Product', 'LocalBusiness'] as $unsupported) {
                $this->assertStringNotContainsString('"'.$unsupported.'"', $json);
            }
        }
    }

    public function test_query_parameters_do_not_create_different_page_identities(): void
    {
        $json = app(PageSchema::class)->toJson('contact');
        $this->get('/contact?type=supply&utm_source=test')->assertSee($json, false);
        $this->assertStringNotContainsString('?type=', $json);
    }

    public function test_json_is_safe_to_embed_and_still_round_trips_content(): void
    {
        $title = '</script><script>alert("test")</script> & trade';
        config(['website.about.title' => $title]);
        $json = app(PageSchema::class)->toJson('about');
        $this->assertStringNotContainsString('</script>', $json);
        $schema = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame($title, array_column($schema['@graph'], null, '@id')[route('about').'#webpage']['name']);
    }

    public function test_schema_preserves_the_prelaunch_and_legal_indexing_gates(): void
    {
        config(['juanovio.legal_reviewed' => false]);
        $this->get('/')->assertSee('noindex, nofollow')->assertSee('application/ld+json');
        config(['juanovio.legal_reviewed' => true]);
        $this->get('/')->assertDontSee('noindex, nofollow');
        foreach (['privacy', 'terms'] as $page) {
            $this->get('/'.$page)->assertSee('noindex, nofollow')->assertSee('application/ld+json');
        }
    }
}
