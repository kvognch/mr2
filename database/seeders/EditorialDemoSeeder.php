<?php

namespace Database\Seeders;

use App\Models\BlogArticle;
use App\Models\BlogRubric;
use App\Models\GeoUnit;
use App\Models\NewsPublication;
use App\Models\NewsRubric;
use App\Models\ResourceType;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/** Explicit, local demo content; deliberately not included in DatabaseSeeder. */
class EditorialDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $directions = ResourceType::pluck('id', 'slug');
            $regions = GeoUnit::where('admin_level', 4)->whereIn('name', ['Санкт-Петербург', 'Ленинградская область'])->get();
            $blog = [
                ['Как подключить объект к электрическим сетям', 'От определения сетевой организации до договора и фактической подачи напряжения: документы, сроки и основные этапы.', 'electricity', 'connection'],
                ['Документы для технологического присоединения', 'Что подготовить для подачи заявки на подключение электричества.', 'electricity', 'documents'],
                ['Как подключить объект к водопроводу', 'Основные этапы и вопросы перед подачей заявки на подключение воды.', 'external-water-supply', 'connection'],
                ['Подключение к канализации: с чего начать', 'Как подготовить исходные данные и найти подходящую организацию.', 'external-sewerage', 'connection'],
                ['Технические условия на теплоснабжение', 'Какие сведения проверить в полученных документах.', 'heat-supply', 'documents'],
                ['Документы для газификации объекта', 'Перечень сведений и документов для первичного обращения.', 'gas-supply', 'documents'],
                ['Проектирование наружных электрических сетей', 'Что уточнить перед подготовкой проекта и выбором проектировщика.', 'electricity', 'design-construction'],
                ['Строительство наружного водопровода', 'Как организовать работы и приёмку инженерной сети.', 'external-water-supply', 'design-construction'],
                ['Как выбрать проектировщика тепловых сетей', 'Проверяем опыт исполнителя и состав проектных работ.', 'heat-supply', 'design-construction'],
            ];
            $articles = [];
            foreach ($blog as $i => [$title,$excerpt,$direction,$rubric]) {
                $item = BlogArticle::firstOrCreate(['slug' => 'demo-blog-'.($i + 1)], ['title' => $title, 'excerpt' => $excerpt, 'rubric_id' => BlogRubric::where('slug', $rubric)->firstOrFail()->id, 'status' => 'published', 'published_at' => now()->subDays($i + 1), 'content_updated_at' => now()->subDays($i), 'use_rich_editor' => false, 'body' => view('editorial.demo.article', compact('title', 'excerpt'))->render(), 'cta' => ['enabled' => true, 'title' => 'Найдите организацию для своей задачи', 'text' => 'Откройте карту: направление уже будет выбрано.', 'button' => 'Показать организации на карте', 'direction_id' => $directions[$direction] ?? null]]);
                if ($item->wasRecentlyCreated) {
                    $item->directions()->sync(array_filter([$directions[$direction] ?? null]));
                    if ($i % 3 === 0 && $regions->isNotEmpty()) {
                        $item->regions()->sync([$regions->first()->id]);
                    }
                }
                $articles[] = $item;
            }
            $news = [
                ['Новый порядок подачи заявки на подключение к электросетям', 'Кратко показываем изменения в составе документов и последовательности действий.', 'electricity', 'laws', 'Заявители и подрядчики'],
                ['Обновлены параметры платы за подключение к тепловым сетям', 'Что изменилось в расчёте и где проверить параметры для конкретного объекта.', 'heat-supply', 'tariffs', 'Застройщики и владельцы объектов'],
                ['В районе введён новый участок наружного водопровода', 'На карте можно уточнить территорию и возможные точки подключения.', 'external-water-supply', 'infrastructure', 'Собственники участков и проектировщики'],
                ['Компания открыла дополнительный центр приёма заявок', 'Адрес, режим работы и перечень вопросов для обращения.', 'gas-supply', 'companies', 'Заявители и представители организаций'],
                ['Сетевая организация обновила личный кабинет заявителя', 'Где отслеживать статус заявки и загружать документы.', 'electricity', 'companies', 'Действующие заявители организации'],
                ['Уточнены сроки действия технических условий', 'Как проверить дату окончания и что делать, если работы не завершены вовремя.', 'external-sewerage', 'laws', 'Заказчики и исполнители подключения'],
                ['Изменился порядок подачи заявки на технологическое присоединение', 'Разбираем, кого затрагивает изменение и что проверить перед обращением.', 'electricity', 'laws', 'Владельцы объектов и подрядчики'],
                ['Обновляется форма одного из обязательных документов', 'Что проверить в комплекте документов перед отправкой заявки.', 'gas-supply', 'laws', 'Заявители и проектировщики'],
                ['Начинают применяться новые параметры платы за подключение', 'Проверьте предварительный расчёт по объектам в работе.', 'heat-supply', 'tariffs', 'Владельцы объектов и застройщики'],
            ];
            foreach ($news as $i => [$title,$excerpt,$direction,$rubric,$audience]) {
                $federal = $i % 3 === 2;
                $item = NewsPublication::firstOrCreate(['slug' => 'demo-news-'.($i + 1)], ['title' => $title, 'excerpt' => $excerpt, 'rubric_id' => NewsRubric::where('slug', $rubric)->firstOrFail()->id, 'status' => 'published', 'published_at' => now()->subDays($i)->subHour(), 'use_rich_editor' => false, 'is_federal' => $federal, 'audience' => $audience, 'source_name' => 'Демонстрационный пример из ТЗ, не официальная публикация', 'body' => view('editorial.demo.news', compact('title', 'excerpt', 'audience'))->render(), 'cta' => ['enabled' => true, 'title' => 'Найдите организацию для подключения', 'text' => 'Перейдите к организациям выбранного направления на карте.', 'button' => 'Показать организации на карте', 'direction_id' => $directions[$direction] ?? null]]);
                if ($item->wasRecentlyCreated) {
                    $item->directions()->sync(array_filter([$directions[$direction] ?? null]));
                    if (! $federal && $regions->isNotEmpty()) {
                        $item->regions()->sync([$regions[$i % $regions->count()]->id]);
                    }$related = [];
                    foreach (array_slice($articles, $i % 6, 3) as $position => $article) {
                        $related[$article->id] = ['sort_order' => $position];
                    }$item->relatedArticles()->sync($related);
                }
            }
            Setting::firstOrCreate(['key' => 'blog.home'], ['value' => json_encode(['interval' => 7, 'slides' => array_map(fn ($a) => ['article_id' => $a->id], array_slice($articles, 0, 3))], JSON_UNESCAPED_UNICODE)]);
        });
    }
}
