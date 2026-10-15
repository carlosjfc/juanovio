<?php

namespace Tests\Feature;

use DOMDocument;
use DOMXPath;
use Tests\TestCase;

class MarketContentTest extends TestCase
{
    public function test_market_lists_have_exact_codes_names_and_order_in_both_languages(): void
    {
        $expected = [
            'en' => [
                'sourcing' => ['DO' => 'Dominican Republic', 'PA' => 'Panama', 'GT' => 'Guatemala', 'CO' => 'Colombia', 'BR' => 'Brazil', 'TR' => 'Türkiye (Turkey)'],
                'destination' => ['GY' => 'Guyana', 'JM' => 'Jamaica', 'SR' => 'Suriname', 'BS' => 'Bahamas', 'BZ' => 'Belize', 'GD' => 'Grenada', 'AG' => 'Antigua and Barbuda'],
            ],
            'es' => [
                'sourcing' => ['DO' => 'República Dominicana', 'PA' => 'Panamá', 'GT' => 'Guatemala', 'CO' => 'Colombia', 'BR' => 'Brasil', 'TR' => 'Turquía'],
                'destination' => ['GY' => 'Guyana', 'JM' => 'Jamaica', 'SR' => 'Surinam', 'BS' => 'Bahamas', 'BZ' => 'Belice', 'GD' => 'Granada', 'AG' => 'Antigua y Barbuda'],
            ],
        ];
        foreach ($expected as $locale => $groups) {
            $response = $this->get($locale === 'en' ? '/markets' : '/es/markets')->assertOk();
            $document = new DOMDocument;
            $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
            $xpath = new DOMXPath($document);
            foreach ($groups as $group => $countries) {
                $this->assertSame($countries, config('juanovio.markets.'.$group.'.countries'));
                $items = $xpath->query('//*[@data-market="'.$group.'"]/*[local-name()="li"]');
                $rendered = [];
                foreach ($items as $item) {
                    $rendered[$item->getAttribute('data-country')] = trim($item->textContent);
                }
                $this->assertSame($countries, $rendered);
            }
            $this->assertSame(1, $xpath->query('//*[local-name()="aside" and contains(@class, "disclosure")]')->length);
            $response->assertDontSee('pilot')->assertDontSee('Phase')->assertDontSee('United States');
        }
    }

    public function test_home_summary_links_to_localized_markets_and_categories_share_the_same_copy(): void
    {
        foreach (['en' => '', 'es' => '/es'] as $locale => $prefix) {
            $home = $this->get($prefix ?: '/')->assertOk();
            $home->assertSee(route($locale === 'en' ? 'markets' : 'es.markets'), false)
                ->assertSee(__('Explore Our Markets'))->assertDontSee('Guyana')->assertDontSee('data-market=', false);
            $categories = config('juanovio.categories');
            $trade = $this->get($prefix.'/trade')->assertOk();
            foreach ($categories as $category) {
                $home->assertSee($category['title'])->assertSee($category['text']);
                $trade->assertSee($category['title'])->assertSee($category['text']);
            }
            $trade->assertSee(__('Product availability, origins, specifications, quantities, and commercial terms are subject to confirmation. This page is not a product catalog or binding offer.'));
        }
    }

    public function test_markets_ctas_and_language_switch_preserve_the_page_and_inquiry_type(): void
    {
        foreach (['en' => '', 'es' => '/es'] as $locale => $prefix) {
            $response = $this->get($prefix.'/markets')->assertOk();
            foreach (['buy' => "I'm a Buyer or Importer", 'supply' => "I'm a Producer or Supplier"] as $type => $label) {
                $url = route($locale === 'en' ? 'contact' : 'es.contact', ['type' => $type]);
                $response->assertSee($url, false)->assertSee(__($label, [], $locale));
                $this->get($url)->assertOk()->assertSee('&quot;type&quot;:&quot;'.$type.'&quot;', false);
                $other = $locale === 'en' ? 'es' : 'en';
                $this->get('/language/'.$other.'?page=contact&type='.$type)
                    ->assertRedirect(route($other === 'en' ? 'contact' : 'es.contact', ['type' => $type]));
            }
            $this->get('/language/'.$locale.'?page=markets')->assertRedirect(route($locale === 'en' ? 'markets' : 'es.markets'));
        }
    }

    public function test_titles_descriptions_and_social_metadata_use_the_requested_localized_copy(): void
    {
        $expected = [
            '/' => ['Juanovio | International Sourcing & Trade Development', 'Explore sourcing and trade opportunities across multiple origins and Caribbean destination markets. Connect with Juanovio as a buyer, supplier, or partner.'],
            '/es' => ['Juanovio | Abastecimiento y comercio internacional', 'Explore oportunidades de abastecimiento y comercio entre distintos países de origen y mercados del Caribe. Conecte con Juanovio como comprador o proveedor.'],
            '/markets' => ['Sourcing & Destination Markets | Juanovio', "Explore Juanovio's sourcing interests and destination markets across the Caribbean and the Americas, with selected international origins under evaluation."],
            '/es/markets' => ['Mercados de origen y destino | Juanovio', 'Conozca los mercados de origen y destino de Juanovio en el Caribe y las Américas, con otros orígenes internacionales en evaluación.'],
            '/trade' => ['Trade Categories & Sourcing | Juanovio', 'Cement-focused market development, with other construction materials, flour, and commercial goods evaluated case by case. Discuss a sourcing opportunity.'],
            '/es/trade' => ['Productos y abastecimiento internacional | Juanovio', 'Desarrollo comercial enfocado en cemento, con otros materiales, harina y bienes comerciales evaluados caso por caso. Consulte sobre abastecimiento.'],
        ];
        foreach ($expected as $path => [$title, $description]) {
            $response = $this->get($path)->assertOk()->assertSee('<title>'.e($title).'</title>', false);
            foreach (['name="description"', 'property="og:description"', 'name="twitter:description"'] as $attribute) {
                $response->assertSee('<meta '.$attribute.' content="'.e($description).'">', false);
            }
            foreach (['property="og:title"', 'name="twitter:title"'] as $attribute) {
                $response->assertSee('<meta '.$attribute.' content="'.e($title).'">', false);
            }
        }
    }

    public function test_contact_explains_locations_without_duplicate_fields_or_changing_bindings(): void
    {
        foreach (['/contact', '/es/contact'] as $path) {
            $response = $this->get($path)->assertOk()->assertSee(__('Your country'))
                ->assertSee(config('website.contact.help'))
                ->assertSee(__('Complete either or both locations. Leave blank if the location is still open.'));
            foreach (['country', 'origin', 'destination'] as $field) {
                $this->assertSame(1, substr_count($response->getContent(), 'wire:model="'.$field.'"'));
            }
        }
    }
}
