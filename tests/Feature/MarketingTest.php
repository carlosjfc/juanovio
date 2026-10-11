<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingTest extends TestCase
{
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
