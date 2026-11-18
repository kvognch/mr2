<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Pages\BlogHome;
use App\Filament\Resources\BlogArticleResource\Pages\CreateRecord;
use App\Filament\Resources\BlogArticleResource\Pages\ListRecords;
use App\Filament\Resources\BlogRubricResource\Pages\EditRecord;
use App\Models\BlogArticle;
use App\Models\BlogRubric;
use App\Models\GeoUnit;
use App\Models\NewsPublication;
use App\Models\NewsRubric;
use App\Models\ResourceType;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditorialReleaseTest extends TestCase
{
    use DatabaseTransactions;

    private function article(array $data = []): BlogArticle
    {
        return BlogArticle::create(array_merge(['title' => 'Аудит '.uniqid(), 'slug' => 'audit-'.uniqid(), 'rubric_id' => BlogRubric::firstOrFail()->id, 'status' => 'published', 'published_at' => now()->subHour(), 'body' => '<p>Текст аудита</p>'], $data));
    }

    private function login(): void
    {
        $this->actingAs(User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]));
    }

    public function test_bulk_withdraw_and_delete_are_scoped_to_selected_records(): void
    {
        $this->login();
        $a = $this->article();
        $b = $this->article();
        $preserved = $this->article();
        Livewire::test(ListRecords::class)->callTableBulkAction('withdraw', [$a, $b])->assertHasNoActionErrors();
        $this->assertSame('withdrawn', $a->fresh()->status);
        $this->assertSame('withdrawn', $b->fresh()->status);
        $this->assertSame('published', $preserved->fresh()->status);
        $c = Livewire::test(ListRecords::class)->mountTableBulkAction('delete', [$a, $b]);
        $this->assertSame('Будет удалено публикаций: 2. Подтвердите удаление.', $c->instance()->getMountedAction()->getModalDescription());
        $c->callMountedAction()->assertHasNoActionErrors();
        $this->assertNull($a->fresh());
        $this->assertNull($b->fresh());
        $this->assertNotNull($preserved->fresh());
    }

    public function test_publishing_in_visual_editor_and_validation(): void
    {
        $this->login();
        $page = CreateRecord::class;
        Livewire::test($page)->set('data.title', 'Визуальная публикация')->set('data.slug', 'audit-visual')->set('data.rubric_id', BlogRubric::first()->id)->set('data.status', 'published')->set('data.published_at', now()->subHour()->format('Y-m-d H:i:s'))->set('data.body', ['type' => 'doc', 'content' => [['type' => 'paragraph', 'content' => [['type' => 'text', 'text' => 'Визуальный текст']]]]])->call('create')->assertHasNoFormErrors();
        $a = BlogArticle::where('slug', 'audit-visual')->firstOrFail();
        $this->get($a->public_url)->assertOk()->assertSee('Визуальный текст');
        Livewire::test($page)->set('data.title', 'Нет даты')->set('data.slug', 'audit-incomplete')->set('data.rubric_id', BlogRubric::first()->id)->set('data.status', 'published')->set('data.use_rich_editor', false)->set('data.body_html', '<p>Текст</p>')->call('create')->assertHasFormErrors();
        $this->assertFalse(BlogArticle::where('slug', 'audit-incomplete')->exists());
    }

    public function test_rubric_management_order_and_visibility(): void
    {
        $this->login();
        Livewire::test(\App\Filament\Resources\BlogRubricResource\Pages\CreateRecord::class)->set('data.name', 'Рубрика аудита')->set('data.slug', 'audit-rubric')->set('data.sort_order', -10)->set('data.is_visible', true)->call('create')->assertHasNoFormErrors();
        $rubric = BlogRubric::where('slug', 'audit-rubric')->firstOrFail();
        $a = $this->article(['rubric_id' => $rubric->id]);
        $this->assertSame($rubric->id, $this->get('/blog')->viewData('rubrics')->first()->id);
        Livewire::test(EditRecord::class, ['record' => $rubric->id])->set('data.name', 'Изменённая рубрика')->set('data.is_visible', false)->call('save')->assertHasNoFormErrors();
        $this->assertFalse($this->get('/blog')->viewData('rubrics')->contains('id', $rubric->id));
        $this->assertSame($a->id, $this->get('/blog?rubric=audit-rubric')->viewData('items')->first()->id);
    }

    public function test_related_order_removal_sources_and_cta_switch(): void
    {
        $this->login();
        $a = $this->article();
        $b = $this->article();
        $n = NewsPublication::create(['title' => 'Новость аудита', 'slug' => 'audit-news', 'rubric_id' => NewsRubric::first()->id, 'status' => 'published', 'published_at' => now()->subHour(), 'body' => '<p>Текст</p>', 'use_rich_editor' => false]);
        $page = \App\Filament\Resources\NewsPublicationResource\Pages\EditRecord::class;
        Livewire::test($page, ['record' => $n->id])->set('data.related_rows', [['article_id' => $b->id], ['article_id' => $a->id]])->set('data.source_url', 'https://example.org/source')->set('data.cta.enabled', true)->set('data.cta.title', 'Настроенный переход')->set('data.cta.button', 'На карту')->set('data.cta.direction_id', ResourceType::first()->id)->set('data.cta.region_id', GeoUnit::where('admin_level', 4)->first()->id)->call('save')->assertHasNoFormErrors();
        $n->refresh();
        $this->assertSame([$b->id, $a->id], $n->relatedArticles->pluck('id')->all());
        $this->get($n->public_url)->assertOk()->assertSee('Открыть первоисточник')->assertSee('Настроенный переход');
        Livewire::test($page, ['record' => $n->id])->set('data.related_rows', [])->set('data.cta.enabled', false)->set('data.source_url', null)->call('save')->assertHasNoFormErrors();
        $this->get($n->public_url)->assertDontSee('Материалы по теме')->assertDontSee('Настроенный переход')->assertDontSee('Открыть первоисточник');
    }

    public function test_zero_one_and_ordered_carousel_and_no_redundant_reset(): void
    {
        $a = $this->article();
        $b = $this->article();
        foreach ([[], [$a->id], [$b->id, $a->id]] as $ids) {
            Setting::updateOrCreate(['key' => 'blog.home'], ['value' => json_encode(['interval' => 8, 'slides' => array_map(fn ($id) => ['article_id' => $id], $ids)])]);
            $r = $this->get('/blog')->assertOk();
            $this->assertSame($ids, $r->viewData('slides')->pluck('id')->all());
            if (count($ids) < 2) {
                $r->assertDontSee('aria-label="Слайд', false);
            }if (! $ids) {
                $r->assertDontSee('data-editorial-block="carousel"', false);
            }
        }
        foreach (['/blog', '/news'] as $url) {
            $this->get($url)->assertOk()->assertDontSee('Сбросить фильтры');
        }
        $this->get('/news?rubric=unknown-audit')->assertOk()->assertSee('Сбросить фильтры');
    }

    public function test_image_upload_invalid_source_and_client_access(): void
    {
        $this->login();
        Storage::fake('public');
        $page = \App\Filament\Resources\NewsPublicationResource\Pages\CreateRecord::class;
        Livewire::test($page)->set('data.title', 'Новость с изображением')->set('data.slug', 'audit-upload')
            ->set('data.rubric_id', NewsRubric::first()->id)->set('data.status', 'draft')
            ->set('data.image', [UploadedFile::fake()->image('preview.jpg', 1200, 630)])
            ->call('create')->assertHasNoFormErrors();
        $n = NewsPublication::where('slug', 'audit-upload')->firstOrFail();
        $this->assertNotEmpty($n->image);
        Storage::disk('public')->assertExists($n->image);
        $n->update(['status' => 'published', 'published_at' => now()->subHour(), 'body' => '<p>Новость с обложкой</p>']);
        $this->get('/news')->assertOk()->assertSee($n->image_url, false);
        Livewire::test($page)->set('data.title', 'Некорректный источник')->set('data.slug', 'audit-url')
            ->set('data.rubric_id', NewsRubric::first()->id)->set('data.source_url', 'javascript:alert(1)')->call('create')->assertHasFormErrors(['source_url']);
        $this->actingAs(User::factory()->create(['role' => UserRole::Client, 'is_active' => true]));
        foreach (['/dashboard/blog-home', '/dashboard/blog-articles/create', '/dashboard/news-publications/create', '/dashboard/news-rubrics', '/dashboard/news-publications/'.$n->id.'/edit'] as $url) {
            $this->get($url)->assertForbidden();
        }
    }

    public function test_withdrawn_selections_do_not_block_admin_saving_or_change_news_carousel_settings(): void
    {
        $this->login();
        $withdrawn = $this->article();
        $published = $this->article();
        Setting::updateOrCreate(['key' => 'blog.home'], ['value' => json_encode(['interval' => 60, 'slides' => [['article_id' => $withdrawn->id], ['article_id' => $published->id]]])]);
        $news = NewsPublication::create(['title' => 'Проверка снятых связей', 'slug' => 'audit-withdrawn-relations', 'rubric_id' => NewsRubric::first()->id, 'status' => 'published', 'published_at' => now()->subHour(), 'body' => '<p>Текст</p>', 'use_rich_editor' => false]);
        $news->relatedArticles()->attach($withdrawn, ['sort_order' => 0]);
        $news->relatedArticles()->attach($published, ['sort_order' => 1]);
        $withdrawn->update(['status' => 'withdrawn']);
        $this->get('/news')->assertOk()->assertSee('interval: 7000', false)->assertDontSee('interval: 60000', false);
        Livewire::test(BlogHome::class)->set('data.interval', 9)->call('save')->assertHasNoFormErrors();
        $this->assertSame([$published->id], array_column(json_decode(Setting::where('key', 'blog.home')->value('value'), true)['slides'], 'article_id'));
        Livewire::test(\App\Filament\Resources\NewsPublicationResource\Pages\EditRecord::class, ['record' => $news->id])->set('data.title', 'Сохранённая правка')->call('save')->assertHasNoFormErrors();
        $this->assertSame([$published->id], $news->fresh()->relatedArticles->pluck('id')->all());
    }
}
