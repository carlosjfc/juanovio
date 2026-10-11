<?php

namespace Tests\Feature;

use Tests\TestCase;

class MarketingTest extends TestCase
{
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
