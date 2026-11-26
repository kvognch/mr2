<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Contractor;
use App\Models\InformationPage;
use App\Models\User;
use App\Support\HomepageSettings;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class LegalPagesAndConsentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_documents_are_published_and_linked_from_the_legal_page_body(): void
    {
        $offer = $this->get('/info/offer')->assertOk()->assertSee('Договор-оферта')
            ->assertSee('Редакция от 30.10.2026 года.')->assertSee('Исполнитель:');
        $this->assertSame(5, $this->xpath($offer->getContent())->query('//main//h2')->length);

        $policy = $this->get('/info/privacy-policy')->assertOk()->assertSee('Политика обработки персональных данных')
            ->assertSee('18. Согласие на обработку персональных данных')
            ->assertSee('20. Реквизиты и контактная информация');
        $this->assertSame(20, $this->xpath($policy->getContent())->query('//main//h2')->length);

        $legal = $this->get('/info/legal')->assertOk();
        $dom = $this->xpath($legal->getContent());
        foreach (['/info/offer', '/info/privacy-policy'] as $url) {
            $this->assertSame(1, $dom->query("//main//a[@href='{$url}']")->length);
            $this->assertSame(0, $dom->query("//footer//a[contains(@href, '{$url}')]")->length);
        }

        // Reapplying the import keeps both existing links and editor changes.
        $page = InformationPage::where('slug', 'legal')->firstOrFail();
        $body = '<p>Текст редактора</p>'.$page->body;
        $page->update(['body' => $body]);
        (require database_path('migrations/2026_11_26_000100_publish_legal_information_pages.php'))->up();
        $this->assertSame($body, $page->fresh()->body);
    }

    public function test_consent_is_required_in_each_public_submission_form_except_login(): void
    {
        $company = Contractor::create(['short_name' => 'Организация для проверки согласия', 'status' => 'approved']);
        $this->actingAs(User::factory()->create(['role' => UserRole::Client, 'is_active' => true]));
        $home = $this->xpath($this->get('/')->assertOk()->getContent());
        $card = $this->xpath($this->get(route('organizations.show', $company->slug))->assertOk()->getContent());

        foreach ([
            [$home, route('request.store')],
            [$home, route('register.store')],
            [$home, route('reviews.service.store')],
            [$card, route('reviews.contractor.store', $company->slug)],
        ] as [$dom, $action]) {
            $form = "//form[@action='{$action}']";
            $this->assertSame(1, $dom->query($form.'//input[@type="checkbox"][@name="privacy_consent"][@required][not(@checked)]')->length);
            $this->assertSame(1, $dom->query($form.'//label[a or span/a]//a[contains(@href, "/info/privacy-policy")][@target="_blank"]')->length);
        }

        $this->assertSame(0, $home->query('//form[@action="'.route('login.attempt').'"]//input[@name="privacy_consent"]')->length);
    }

    public function test_all_four_endpoints_validate_consent_before_saving(): void
    {
        $this->withoutMiddleware(VerifyCsrfToken::class);
        HomepageSettings::save(['google_recaptcha' => ['secret_key' => 'test-secret']]);
        Http::fake(['https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true])]);

        $company = Contractor::create(['short_name' => 'Организация для отзыва о согласии', 'status' => 'approved']);
        $actor = User::factory()->create(['role' => UserRole::Client, 'is_active' => true]);
        $captcha = ['g-recaptcha-response' => 'test-token'];
        $request = $captcha + ['name' => 'Проверка заявки', 'phone' => '+7 (999) 180-77-01', 'comment' => null, 'source_url' => null];
        $registration = $captcha + ['name' => 'Проверка регистрации', 'email' => 'privacy-consent@example.test', 'phone' => '+7 (999) 180-77-02', 'password' => 'test-password', 'password_confirmation' => 'test-password'];
        $review = ['author_name' => 'Автор', 'author_role' => 'Пользователь', 'title' => 'Отзыв о согласии', 'body' => 'Проверка отправки с согласием.', 'rating' => 5];

        foreach (['/request' => $request, '/register' => $registration] as $url => $payload) {
            $this->postJson($url, $payload)->assertUnprocessable()->assertJsonValidationErrors('privacy_consent');
            $this->postJson($url, $payload + ['privacy_consent' => '0'])->assertUnprocessable()->assertJsonValidationErrors('privacy_consent');
        }
        $this->assertDatabaseMissing('service_requests', ['name' => $request['name']]);
        $this->assertDatabaseMissing('users', ['email' => $registration['email']]);
        $this->postJson('/request', $request + ['privacy_consent' => '1'])->assertOk();
        $this->post('/register', $registration + ['privacy_consent' => '1'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('service_requests', ['name' => $request['name']]);
        $this->assertDatabaseHas('users', ['email' => $registration['email']]);

        $this->post('/login', ['identifier' => $actor->email, 'password' => 'password'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertAuthenticatedAs($actor);
        foreach (['/reviews/service' => 'service_reviews', '/reviews/contractor/'.$company->slug => 'contractor_reviews'] as $url => $table) {
            $this->postJson($url, $review)->assertUnprocessable()->assertJsonValidationErrors('privacy_consent');
            $this->assertDatabaseMissing($table, ['title' => $review['title'], 'user_id' => $actor->id]);
            $this->postJson($url, $review + ['privacy_consent' => '1'])->assertOk();
            $this->assertDatabaseHas($table, ['title' => $review['title'], 'user_id' => $actor->id]);
        }
    }

    private function xpath(string $html): DOMXPath
    {
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);

        return new DOMXPath($document);
    }
}
