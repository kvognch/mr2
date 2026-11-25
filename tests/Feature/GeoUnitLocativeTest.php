<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\GeoUnitResource\Pages\EditGeoUnit;
use App\Models\GeoUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class GeoUnitLocativeTest extends TestCase
{
    use DatabaseTransactions;

    private function unit(string $name, ?string $phrase = null): GeoUnit
    {
        return GeoUnit::create([
            'name' => $name,
            'name_locative' => $phrase,
            'normalized_name' => $name,
            'source' => 'locative-test',
            'source_id' => uniqid(),
        ]);
    }

    public function test_backfill_uses_names_preserves_overrides_and_leaves_unknown_names_empty(): void
    {
        $petersburg = $this->unit('Санкт-Петербург');
        $district = $this->unit('Всеволожский район');
        $islands = $this->unit('Территория Залитских островов');
        $manual = $this->unit('Карелия', 'в Республике Карелия');
        $unknown = $this->unit('Неизвестное словарю название');
        $migration = require database_path('migrations/2026_11_25_180100_fill_geo_unit_locatives.php');
        $migration->up();

        $this->assertSame('в Санкт-Петербурге', $petersburg->fresh()->name_locative);
        $this->assertSame('во Всеволожском районе', $district->fresh()->name_locative);
        $this->assertSame('на территории Залитских островов', $islands->fresh()->name_locative);
        $this->assertSame('в Республике Карелия', $manual->fresh()->name_locative);
        $this->assertNull($unknown->fresh()->name_locative);

        $petersburg->update(['name_locative' => 'в городе Санкт-Петербурге']);
        $migration->up();
        $this->assertSame('в городе Санкт-Петербурге', $petersburg->fresh()->name_locative);
    }

    public function test_admin_can_edit_and_clear_the_optional_phrase(): void
    {
        $unit = $this->unit('Санкт-Петербург', 'в Санкт-Петербурге');
        $this->actingAs(User::factory()->create(['role' => UserRole::Superadmin, 'is_active' => true]));

        Livewire::test(EditGeoUnit::class, ['record' => $unit->id])
            ->assertFormSet(['name_locative' => 'в Санкт-Петербурге'])
            ->set('data.name_locative', 'в городе Санкт-Петербурге')
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertSame('в городе Санкт-Петербурге', $unit->fresh()->name_locative);

        Livewire::test(EditGeoUnit::class, ['record' => $unit->id])
            ->set('data.name_locative', '')
            ->call('save')
            ->assertHasNoFormErrors();
        $this->assertNull($unit->fresh()->name_locative);
    }
}
