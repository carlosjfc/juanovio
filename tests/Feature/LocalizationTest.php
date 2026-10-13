<?php

namespace Tests\Feature;

use App\Livewire\InquiryForm;
use App\Notifications\TradeInquiry;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    public function test_english_is_default_regardless_of_browser_language_or_location_headers(): void
    {
        $this->withHeaders(['Accept-Language' => 'es', 'CF-IPCountry' => 'ES'])->get('/')
            ->assertOk()->assertSee('<html lang="en">', false)->assertSee('Building Connections. Moving Trade Forward.');
    }

    public function test_every_spanish_page_has_translated_content_and_localized_seo(): void
    {
        $pages = require config_path('website.php');
        foreach ($pages as $page => $content) {
            $url = route('es.'.$page);
            $response = $this->get($url)->assertOk()->assertSee('<html lang="es">', false)
                ->assertSee('<link rel="canonical" href="'.$url.'">', false)
                ->assertSee('hreflang="en"', false)->assertSee('hreflang="es"', false)
                ->assertSee('hreflang="x-default"', false)->assertSee('es_ES')
                ->assertSee(__($content['heading']))->assertDontSee($content['heading']);
            $response->assertSee('Elegir idioma')->assertSee('data-language-choice="en"', false);
        }
        $this->get('/es/contact?type=supply')->assertSee('Correo electrónico profesional')->assertSee('Presentación de proveedor');
        $this->get('/')->assertSee('<html lang="en">', false)->assertSee('Building Connections. Moving Trade Forward.');
    }

    public function test_all_configured_content_has_a_spanish_translation(): void
    {
        $translations = json_decode(file_get_contents(lang_path('es.json')), true, flags: JSON_THROW_ON_ERROR);
        $assertTranslated = function (array $values) use (&$assertTranslated, $translations): void {
            foreach ($values as $value) {
                if (is_array($value)) {
                    $assertTranslated($value);
                } elseif (is_string($value)) {
                    $this->assertArrayHasKey($value, $translations);
                    $this->assertNotSame('', $translations[$value]);
                }
            }
        };
        $assertTranslated(require config_path('website.php'));
        $brand = require config_path('juanovio.php');
        foreach (['navigation', 'types', 'quantity_units', 'categories', 'markets'] as $key) {
            $assertTranslated($brand[$key]);
        }
    }

    public function test_explicit_choice_preserves_page_and_inquiry_type_and_sets_secure_cookie(): void
    {
        $response = $this->get('/language/es?page=contact&type=supply')->assertRedirect(route('es.contact', ['type' => 'supply']))
            ->assertCookie('juanovio_language', 'es');
        $cookie = collect($response->headers->getCookies())->first(fn ($cookie) => $cookie->getName() === 'juanovio_language');
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
        $this->assertGreaterThan(now()->addMonths(11)->timestamp, $cookie->getExpiresTime());
        $this->withCookie('juanovio_language', 'es')->get('/trade')->assertRedirect(route('es.trade'));
        $this->withCookie('juanovio_language', 'es')->get('/language/en?page=trade')
            ->assertRedirect(route('trade'))->assertCookie('juanovio_language', 'en');
    }

    public function test_invalid_choices_and_redirect_targets_are_rejected(): void
    {
        $this->get('/language/fr')->assertNotFound();
        $this->get('/language/es?page=https://example.com')->assertNotFound();
        $this->get('/language/es?page[]=contact')->assertNotFound();
        $this->withCookie('juanovio_language', 'invalid')->get('/')->assertOk()->assertSee('<html lang="en">', false);
    }

    public function test_spanish_livewire_requests_keep_their_locale_and_translate_validation_and_success(): void
    {
        Notification::fake();
        config(['juanovio.inquiries_enabled' => true, 'juanovio.legal_reviewed' => true,
            'juanovio.recipient' => 'inquiries@example.test', 'mail.default' => 'smtp']);
        app()->setLocale('es');
        $form = Livewire::test(InquiryForm::class)->set('type', 'general');
        $this->travel(4)->seconds();
        $form->call('submit')->assertHasErrors(['name', 'email', 'consent'])->assertSee('es obligatorio');
        $form->set('name', 'Persona de prueba')->set('company', 'Empresa de prueba')
            ->set('email', 'buyer@example.test')->set('country', 'España')
            ->set('message', 'Nos gustaría conversar sobre una oportunidad comercial.')->set('consent', true)
            ->call('submit')->assertHasNoErrors()->assertSee('Hemos recibido su consulta.');
        Notification::assertSentOnDemand(TradeInquiry::class);
    }
}
