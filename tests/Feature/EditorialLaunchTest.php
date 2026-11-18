<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\BlogHome;
use App\Filament\Resources\BlogArticleResource\Pages\CreateRecord;
use App\Filament\Resources\NewsPublicationResource\Pages\EditRecord;
use App\Models\BlogArticle;
use App\Models\BlogRubric;
use App\Models\GeoUnit;
use App\Models\NewsPublication;
use App\Models\NewsRubric;
use App\Models\ResourceType;
use App\Models\Setting;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class EditorialLaunchTest extends TestCase
{
    use DatabaseTransactions;

    private function article(array $data = []): BlogArticle
    {
        return BlogArticle::create(array_merge(['title' => 'Проверочная статья', 'slug' => 'launch-'.uniqid(), 'rubric_id' => BlogRubric::firstOrFail()->id, 'status' => 'published', 'published_at' => now()->subDay(), 'body' => '<h2>Инструкция</h2><p>Текст</p>'], $data));
    }

    private function news(array $data = []): NewsPublication
    {
        return NewsPublication::create(array_merge(['title' => 'Проверочная новость', 'slug' => 'launch-'.uniqid(), 'rubric_id' => NewsRubric::firstOrFail()->id, 'status' => 'published', 'published_at' => now()->subDay(), 'body' => '<p>Изменение</p>'], $data));
    }

    public function test_publication_visibility_sitemaps_and_safe_html(): void
    {
        $article = $this->article(['body' => '<p>Безопасный текст</p><script>alert(1)</script><img src="x" onerror="alert(2)">']);
        $this->assertStringNotContainsString('<script', $article->body);
        $this->assertStringNotContainsString('onerror', $article->body);
        $this->get($article->public_url)->assertOk()->assertSee('Безопасный текст');
        $article->update(['status' => 'draft']);
        $this->get($article->public_url)->assertNotFound();
        $this->get('/sitemap-blog.xml')->assertOk()->assertDontSee($article->slug);
        $article->update(['status' => 'published', 'published_at' => now()->addDay()]);
        $this->get($article->public_url)->assertNotFound();
        $news = $this->news();
        $this->get('/sitemap-news.xml')->assertSee($news->slug);
        $this->get('/sitemap.xml')->assertSee('sitemap-blog.xml')->assertSee('sitemap-news.xml');
    }

    public function test_region_semantics_and_combined_filters(): void
    {
        $region = GeoUnit::where('admin_level', 4)->firstOrFail();
        $direction = ResourceType::firstOrFail();
        $universal = $this->article(['title' => 'Универсальная инструкция']);
        $local = $this->article(['title' => 'Местная инструкция']);
        $local->regions()->attach($region);
        $local->directions()->attach($direction);
        $this->get('/blog?region='.$region->id)->assertOk()->assertSee('Универсальная инструкция')->assertSee('Местная инструкция');
        $this->get('/blog?region='.$region->id.'&direction='.$direction->slug)->assertSee('Местная инструкция')->assertDontSee('Универсальная инструкция');
        $unknown = $this->news(['title' => 'Неопределенная территория']);
        $federal = $this->news(['title' => 'Федеральная новость', 'is_federal' => true]);
        $this->get('/news?region='.$region->id)->assertOk()->assertSee('Федеральная новость')->assertDontSee('Неопределенная территория');
    }

    public function test_stable_pagination_related_articles_and_cta(): void
    {
        for ($i = 0; $i < 14; $i++) {
            $this->article(['published_at' => now()->subDay()->startOfDay()]);
        }
        $first = $this->get('/blog')->assertOk()->viewData('items');
        $second = $this->get('/blog?page=2')->assertOk()->assertSee(' - Страница 2')->viewData('items');
        $this->assertCount(12, $first);
        $this->assertEmpty(array_intersect($first->pluck('id')->all(), $second->pluck('id')->all()));
        $this->get('/blog?page=2', ['X-Editorial-More' => '1'])->assertOk()->assertDontSee('<html', false)->assertSee('data-next-url', false);
        $a = $this->article(['title' => 'Связанный материал']);
        $n = $this->news(['cta' => ['enabled' => true, 'title' => 'На карту', 'button' => 'Найти', 'direction_id' => ResourceType::first()->id, 'region_id' => GeoUnit::where('admin_level', 4)->first()->id]]);
        $n->relatedArticles()->attach($a, ['sort_order' => 0]);
        $this->get($n->public_url)->assertOk()->assertSee('Связанный материал')->assertSee('На карту');
        $a->update(['status' => 'withdrawn']);
        $this->get($n->public_url)->assertDontSee('Связанный материал');
        $this->assertStringContainsString('res=', $n->map_url);
        $this->assertStringContainsString('r=', $n->map_url);
    }

    public function test_editorial_admin_forms_render_and_enforce_permissions(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]);
        $this->actingAs($admin);
        foreach (['/dashboard/blog-articles', '/dashboard/blog-articles/create', '/dashboard/news-publications/create', '/dashboard/blog-rubrics', '/dashboard/news-rubrics', '/dashboard/blog-home'] as $url) {
            $this->get($url)->assertOk();
        }
        $a = $this->article();
        $this->get('/dashboard/blog-articles/'.$a->id.'/edit')->assertOk();
        Livewire::test(CreateRecord::class)->set('data.title', 'Черновик из админки')->set('data.slug', 'draft-form-check')->set('data.rubric_id', BlogRubric::first()->id)->set('data.status', 'draft')->call('create')->assertHasNoFormErrors();
        $this->assertTrue(BlogArticle::where('slug', 'draft-form-check')->exists());
        $client = User::factory()->create(['role' => UserRole::Client, 'is_active' => true]);
        $this->actingAs($client)->get('/dashboard/blog-articles')->assertForbidden();
    }

    public function test_raw_html_news_relations_and_home_settings_can_be_saved(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]));
        $article = $this->article();
        Livewire::test(\App\Filament\Resources\NewsPublicationResource\Pages\CreateRecord::class)
            ->set('data.title', 'Новость через HTML')
            ->set('data.slug', 'launch-html-news')
            ->set('data.rubric_id', NewsRubric::first()->id)
            ->set('data.use_rich_editor', false)
            ->set('data.body_html', '<h2>Важно</h2><details><summary>Вопрос</summary><p>Ответ</p></details><script>alert(1)</script>')
            ->set('data.status', 'published')
            ->set('data.published_at', now()->subHour()->format('Y-m-d H:i:s'))
            ->set('data.related_rows', [['article_id' => $article->id]])
            ->set('data.directions', [ResourceType::first()->id])
            ->call('create')->assertHasNoFormErrors();
        $news = NewsPublication::where('slug', 'launch-html-news')->firstOrFail();
        $this->assertFalse($news->use_rich_editor);
        $this->assertStringContainsString('<details>', $news->body);
        Livewire::test(EditRecord::class, ['record' => $news->id])->set('data.title', 'Правка HTML-новости')->call('save')->assertHasNoFormErrors();
        $this->assertStringContainsString('<details>', $news->fresh()->body);
        $this->assertStringNotContainsString('<script', $news->body);
        $this->assertSame([$article->id], $news->relatedArticles->pluck('id')->all());
        $this->assertCount(1, $news->directions);
        $this->get($news->public_url)->assertOk()->assertSee('Материалы по теме');
        Livewire::test(BlogHome::class)
            ->set('data.interval', 9)->set('data.slides', [['article_id' => $article->id]])->call('save')->assertHasNoFormErrors();
        $this->get('/blog')->assertOk()->assertSee('Главные материалы');
        $article->update(['status' => 'withdrawn']);
        $this->get('/blog')->assertDontSee('Главные материалы');
        $this->assertNull($news->content_updated_at);
        $news->update(['title' => 'Техническая правка']);
        $this->assertNull($news->fresh()->content_updated_at);
    }

    public function test_stage_one_block_structure_matches_each_page_type(): void
    {
        $article = $this->article(['cta' => ['enabled' => true, 'title' => 'Следующий шаг', 'button' => 'На карту']]);
        $news = $this->news(['source_url' => 'https://example.org/document', 'cta' => ['enabled' => true, 'title' => 'Найти организации', 'button' => 'На карту']]);
        $news->relatedArticles()->attach($article, ['sort_order' => 0]);
        Setting::updateOrCreate(['key' => 'blog.home'], ['value' => json_encode(['interval' => 7, 'slides' => [['article_id' => $article->id]]])]);
        $this->get('/blog')->assertOk()->assertSeeInOrder([
            'data-editorial-block="carousel"', 'Выберите направление', 'Выберите рубрику', 'Регион',
            'data-editorial-block="results"', 'data-editorial-block="latest-news"',
        ], false);
        $this->get('/news')->assertOk()->assertSeeInOrder([
            'data-editorial-block="carousel"', 'data-editorial-block="filters"', 'data-editorial-block="results"',
        ], false)->assertDontSee('data-editorial-block="latest-news"', false);
        $this->get($article->public_url)->assertOk()->assertSeeInOrder([
            'data-editorial-block="article-cover"', 'data-editorial-block="body"', 'data-editorial-block="cta"',
        ], false)->assertDontSee('data-editorial-block="related"', false)->assertDontSee('Сохранить статью');
        $response = $this->get($news->public_url)->assertOk()->assertSeeInOrder([
            'data-editorial-block="news-header"', 'Открыть первоисточник', 'data-editorial-block="body"',
            'data-editorial-block="related"', 'data-editorial-block="cta"',
        ], false)->assertDontSee('editorial-hero', false);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        $xpath = new \DOMXPath($dom);
        $this->assertSame(1, $xpath->query('//article[@data-editorial-kind="news"]//section[@data-editorial-block="related"]')->length);
        $this->assertSame(1, $xpath->query('//article[@data-editorial-kind="news"]//section[@data-editorial-block="cta"]')->length);
    }

    public function test_editorial_content_classes_anchors_and_admin_group_order(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]);
        $this->actingAs($admin)->get('/dashboard/blog-articles')->assertOk();
        $groups = collect(Filament::getPanel('admin')->getNavigationGroups())->map(fn ($group) => $group->getLabel())->values()->all();
        $users = array_search('Пользователи', $groups, true);
        $this->assertSame('Блог', $groups[$users + 1]);
        $this->assertSame('Новости', $groups[$users + 2]);
        $body = view('editorial.demo.article', ['title' => 'Пример', 'excerpt' => 'Краткое описание'])->render();
        $article = $this->article(['body' => $body]);
        $this->get($article->public_url)->assertOk()->assertSee('blog-content', false)->assertSee('class="blog-toc"', false)->assertSee('href="#organization"', false)->assertSee('id="organization"', false)->assertSee('class="blog-summary"', false)->assertSee('blog-note--warning', false)->assertSee('class="blog-faq"', false);
        $news = $this->news(['body' => view('editorial.demo.news', ['excerpt' => 'Пример', 'audience' => 'Заявители'])->render()]);
        $this->get($news->public_url)->assertOk()->assertSee('news-content', false)->assertSee('class="news-compare"', false);
    }
}
