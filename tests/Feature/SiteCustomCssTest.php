<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\HomepageSettings;
use App\Models\BlogArticle;
use App\Models\BlogRubric;
use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\InformationPage;
use App\Models\NewsPublication;
use App\Models\NewsRubric;
use App\Models\ResourceType;
use App\Models\User;
use App\Support\HomepageSettings as HomepageSettingsStore;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class SiteCustomCssTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_save_change_and_clear_the_public_stylesheet(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]));

        $css = 'body { --css-probe: first; }';
        $saveAppearance = TestAction::make('save_appearance')->schemaComponent('appearance', 'form');
        $form = Livewire::test(HomepageSettings::class)
            ->set('data.appearance.custom_css', $css)
            ->callAction($saveAppearance)
            ->assertHasNoFormErrors();

        $response = $this->get('/site-custom.css')->assertOk()
            ->assertHeader('Content-Type', 'text/css; charset=UTF-8')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Site-CSS-Version', hash('sha256', $css))
            ->assertContent($css);
        $etag = $response->headers->get('ETag');
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $this->assertNotEmpty($etag);
        $this->get('/site-custom.css', ['If-None-Match' => $etag])->assertStatus(304);
        $this->call('HEAD', '/site-custom.css')->assertOk()
            ->assertHeader('ETag', $etag)
            ->assertHeader('X-Site-CSS-Version', hash('sha256', $css))
            ->assertContent('');

        $newCss = 'body { --css-probe: second; }';
        $form->set('data.appearance.custom_css', $newCss)->callAction($saveAppearance)->assertHasNoFormErrors();
        $this->get('/site-custom.css', ['If-None-Match' => $etag])->assertOk()->assertContent($newCss);
        $this->get('/')->assertOk()->assertSee('/site-custom.css?v='.hash('sha256', $newCss), false);

        $form->set('data.appearance.custom_css', '')->callAction($saveAppearance)->assertHasNoFormErrors();
        $this->get('/site-custom.css')->assertOk()->assertContent('');
        $this->actingAs(User::factory()->create(['role' => UserRole::Client, 'is_active' => true]))
            ->get('/dashboard/settings/general')->assertForbidden();
    }

    public function test_public_page_types_link_the_stylesheet_after_the_main_styles(): void
    {
        $css = 'body { --css-probe: public; }';
        HomepageSettingsStore::saveCustomCss($css);
        $article = BlogArticle::create(['title' => 'Проверка CSS', 'slug' => 'css-blog', 'rubric_id' => BlogRubric::firstOrFail()->id, 'body' => '<p>Статья</p>', 'status' => 'published', 'published_at' => now()->subDay()]);
        $news = NewsPublication::create(['title' => 'Проверка CSS', 'slug' => 'css-news', 'rubric_id' => NewsRubric::firstOrFail()->id, 'body' => '<p>Новость</p>', 'status' => 'published', 'published_at' => now()->subDay()]);
        $info = InformationPage::create(['title' => 'Проверка CSS', 'body' => '<p>Информация</p>', 'is_active' => true]);
        $company = Contractor::create(['short_name' => 'Проверка CSS', 'status' => 'approved']);
        $category = ContractorCategory::firstOrFail();
        $direction = ResourceType::firstOrFail();

        foreach ([
            '/', '/search', '/catalog', '/catalog/'.$category->slug, '/directions', '/directions/'.$direction->slug,
            '/organizations/'.$company->slug, '/info/'.$info->slug, '/blog', $article->public_url, '/news', $news->public_url,
        ] as $url) {
            $html = $this->get($url)->assertOk()
                ->assertSee('/site-custom.css?v='.hash('sha256', $css), false)
                ->assertDontSee($css, false)
                ->getContent();
            $this->assertMatchesRegularExpression('/<link[^>]+rel="stylesheet"[^>]*>.*<link[^>]+data-site-custom-css/s', $html, $url);
        }
    }
}
