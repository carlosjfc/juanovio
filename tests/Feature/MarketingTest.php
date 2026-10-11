<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingTest extends TestCase
{
    public function test_only_the_selected_provider_is_exposed_on_every_page(): void
    {
        config(['juanovio.analytics.enabled' => true, 'juanovio.analytics.driver' => 'gtm']);
        foreach (array_keys(config('website')) as $page) {
            $html = $this->get(route($page))->assertOk()->getContent();
            $this->assertSame(1, substr_count($html, 'id="analytics-settings"'));
            $this->assertStringContainsString('GTM-TFMSHHLF', $html);
            $this->assertStringNotContainsString('G-Y1RN3SLGFQ', $html);
            $this->assertStringNotContainsString('googletagmanager.com', $html);
            $this->assertStringContainsString('data-analytics-preferences', $html);
        }
    }

    public function test_direct_provider_and_disabled_or_invalid_configuration(): void
    {
        config(['juanovio.analytics.enabled' => true, 'juanovio.analytics.driver' => 'gtag']);
        $this->get('/')->assertSee('G-Y1RN3SLGFQ')->assertDontSee('GTM-TFMSHHLF');
        config(['juanovio.analytics.enabled' => false]);
        $this->get('/')->assertDontSee('id="analytics-settings"', false)->assertDontSee('data-analytics-preferences');
        config(['juanovio.analytics.enabled' => true, 'juanovio.analytics.ga4_id' => '"><script>alert(1)</script>']);
        $this->get('/')->assertDontSee('id="analytics-settings"', false);
        config(['juanovio.analytics.driver' => 'unsupported']);
        $this->get('/')->assertDontSee('id="analytics-settings"', false);
    }

    public function test_social_metadata_matches_each_page_and_the_approved_image(): void
    {
        $image = config('juanovio.social_image');
        [$width, $height] = getimagesize(public_path($image['path']));
        $this->assertSame($width, $image['width']);
        $this->assertSame($height, $image['height']);
        foreach (config('website') as $page => $content) {
            $this->get(route($page))
                ->assertSee('<meta property="og:site_name" content="Juanovio International Trading Inc.">', false)
                ->assertSee('<meta property="og:locale" content="en_US">', false)
                ->assertSee('<meta property="og:image" content="'.asset($image['path']).'">', false)
                ->assertSee('<meta property="og:image:alt" content="'.e($image['alt']).'">', false)
                ->assertSee('<meta name="twitter:card" content="summary">', false)
                ->assertSee('<meta name="twitter:title" content="'.e($content['title']).'">', false)
                ->assertSee('<meta name="twitter:description" content="'.e($content['description']).'">', false)
                ->assertSee('<meta name="twitter:image" content="'.asset($image['path']).'">', false);
        }
    }

    public function test_all_marketing_pages_render_the_approved_draft_and_metadata(): void
    {
        foreach (config('website') as $page => $content) {
            $this->get(route($page))->assertOk()
                ->assertSee($content['heading'])
                ->assertSee($content['title'])
                ->assertSee('name="description"', false)
                ->assertSee('rel="canonical"', false)
                ->assertSee('noindex, nofollow')
                ->assertDontSee('trusted by leading manufacturers');
        }
    }

    public function test_categories_and_market_statuses_are_conservative(): void
    {
        $this->get('/trade')->assertSee('not a product catalog or binding offer')->assertSee('subject to confirmation');
        $this->get('/markets')->assertSee('Under evaluation')->assertSee('pilot focus, not the full geographic scope');
        $this->get('/about')->assertDontSee('family heritage');
    }

    public function test_indexing_is_disabled_until_review_and_sitemap_uses_canonical_urls(): void
    {
        $this->get('/robots.txt')->assertSee('Disallow: /');
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml')->assertDontSee('<url>', false);
        config(['juanovio.legal_reviewed' => true]);
        $this->get('/sitemap.xml')->assertSee(route('trade'));
    }
}
