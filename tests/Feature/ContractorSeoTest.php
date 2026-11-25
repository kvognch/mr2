<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\ContractorResource\Pages\EditContractor;
use App\Models\Contractor;
use App\Models\ContractorCategory;
use App\Models\GeoUnit;
use App\Models\ResourceType;
use App\Models\User;
use App\Support\ContractorSeo;
use DOMDocument;
use DOMXPath;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class ContractorSeoTest extends TestCase
{
    use DatabaseTransactions;

    private function territory(string $name, int $level, ?string $phrase): GeoUnit
    {
        return GeoUnit::create([
            'source' => 'seo-test',
            'source_id' => uniqid(),
            'name' => $name,
            'normalized_name' => $name,
            'admin_level' => $level,
            'name_locative' => $phrase,
        ]);
    }

    public function test_metadata_uses_one_territory_and_is_escaped_once_in_the_rendered_card(): void
    {
        $city = $this->territory('Санкт-Петербург', 4, 'в Санкт-Петербурге');
        $region = $this->territory('Ленинградская область', 4, 'в Ленинградской области');
        $district = $this->territory('Условный район', 6, 'в Условном районе');
        $company = Contractor::create(['short_name' => 'ООО &quot;МР &amp; <тест>&quot;', 'status' => 'approved']);
        $company->territories()->attach([$district->id, $region->id, $city->id]);
        $company->categories()->attach([
            ContractorCategory::firstOrCreate(['name' => 'Гарантирующие поставщики'])->id,
            ContractorCategory::firstOrCreate(['name' => 'Ресурсо-снабжающие организации'])->id,
        ]);
        $sewer = ResourceType::firstOrCreate(['name' => 'Наружная канализация']);
        $water = ResourceType::firstOrCreate(['name' => 'Наружный водопровод']);
        $company->smrResourceTypes()->attach($sewer);
        $company->pirResourceTypes()->attach([$sewer->id, $water->id]);

        $response = $this->get(route('organizations.show', $company->slug))->assertOk();
        $html = $response->getContent();
        $this->assertStringNotContainsString('&amp;quot;', $html);
        $this->assertStringContainsString('ООО «МР &amp; &lt;тест&gt;»', $html);
        $document = new DOMDocument;
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_NOERROR | LIBXML_NOWARNING);
        $dom = new DOMXPath($document);
        $title = $dom->evaluate('string(//title)');
        $description = $dom->evaluate('string(//meta[@name="description"]/@content)');
        $this->assertStringStartsWith('ООО «МР & <тест>» — ', $title);
        $this->assertStringContainsString('гарантирующий поставщик', $title);
        $this->assertStringContainsString('ресурсо-снабжающая организация', $title);
        $this->assertStringEndsWith('по направлению наружная канализация, наружный водопровод в Санкт-Петербурге — МногоРесурсов', $title);
        $this->assertSame('ООО «МР & <тест>»', $dom->evaluate('string(//h1)'));
        $this->assertSame($title, $dom->evaluate('string(//meta[@property="og:title"]/@content)'));
        $this->assertSame($description, $dom->evaluate('string(//meta[@property="og:description"]/@content)'));
        $this->assertStringEndsWith(' по направлению наружная канализация, наружный водопровод. Информация об организации, направлениях деятельности и территории работы на МногоРесурсов.', $description);
        $this->assertStringNotContainsString('Санкт-Петербург', $description);

        $company->territories()->detach($city);
        $this->assertStringContainsString('в Ленинградской области', ContractorSeo::defaults($company)['seo_title']);
    }

    public function test_missing_parts_and_manual_overrides_are_handled_independently(): void
    {
        $company = Contractor::create(['short_name' => 'ООО «Группа „МР“»']);
        $this->assertSame('ООО «Группа „МР“» — МногоРесурсов', ContractorSeo::resolve($company)['title']);
        $company->territories()->attach($this->territory('Новый регион', 4, null));
        $this->assertSame('ООО «Группа „МР“» — регион: Новый регион — МногоРесурсов', ContractorSeo::resolve($company)['title']);

        $company->seo_title = 'Ручной &quot;Title&quot;';
        $this->assertSame('Ручной "Title"', ContractorSeo::resolve($company)['title']);
        $this->assertStringStartsWith('ООО «Группа „МР“». Информация', ContractorSeo::resolve($company)['description']);
        $company->seo_title = null;
        $company->seo_description = 'Ручное &quot;описание&quot; &amp; текст';
        $this->assertStringContainsString('регион: Новый регион', ContractorSeo::resolve($company)['title']);
        $this->assertSame('Ручное "описание" & текст', ContractorSeo::resolve($company)['description']);

        $resource = ResourceType::create(['name' => 'ГВС Москвы', 'abbreviation' => 'ГВС']);
        $company->smrResourceTypes()->attach($resource);
        $this->assertStringContainsString('ГВС Москвы', ContractorSeo::resolve($company)['title']);
    }

    public function test_name_warning_is_visible_and_does_not_block_saving(): void
    {
        $company = Contractor::create(['short_name' => 'Тестовая компания']);
        $company->categories()->attach(ContractorCategory::firstOrCreate(['name' => 'Подрядчики']));
        $this->actingAs(User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]));
        $name = 'ООО "САНКТ- ПЕТЕРБУРГА';

        Livewire::test(EditContractor::class, ['record' => $company->id])
            ->set('data.short_name', $name)
            ->assertSee('Проверьте пробелы рядом с дефисом')
            ->assertSee('Проверьте вложенные или непарные кавычки')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame($name, $company->fresh()->short_name);
    }
}
