<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Exports\ContractorsExport;
use App\Models\Contractor;
use App\Models\GeoUnit;
use App\Models\User;
use App\Support\ContractorSpreadsheet;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class ContractorExportTest extends TestCase
{
    use DatabaseTransactions;

    public function test_xlsx_preserves_territory_names_without_loading_geometries(): void
    {
        $geo = GeoUnit::create(['source' => 'export-test', 'source_id' => uniqid(), 'name' => 'Тестовый регион экспорта', 'normalized_name' => 'тестовый регион экспорта', 'geometry_osm' => ['type' => 'Polygon', 'coordinates' => [[[1, 2], [2, 3], [1, 2]]]], 'geometry_yandex' => [[[1, 2], [2, 3], [1, 2]]]]);
        $contractor = Contractor::create(['short_name' => 'Тестовая организация экспорта', 'status' => 'approved']);
        $contractor->territories()->attach($geo);
        $event = 'eloquent.retrieved: '.GeoUnit::class;
        $geometriesLoaded = false;
        $dispatcher = GeoUnit::getEventDispatcher();
        $isolated = clone $dispatcher;
        GeoUnit::setEventDispatcher($isolated);
        $isolated->listen($event, function (GeoUnit $unit) use (&$geometriesLoaded) {
            $geometriesLoaded = $geometriesLoaded || array_key_exists('geometry_osm', $unit->getAttributes()) || array_key_exists('geometry_yandex', $unit->getAttributes());
        });
        try {
            $rows = (new ContractorsExport)->array();
        } finally {
            GeoUnit::setEventDispatcher($dispatcher);
        }
        $this->assertFalse($geometriesLoaded);
        $headers = ContractorSpreadsheet::headings();
        $row = collect($rows)->first(fn ($row) => $row[0] === $contractor->id);
        $this->assertSame('Тестовый регион экспорта', $row[array_search(ContractorSpreadsheet::COLUMN_TERRITORIES, $headers, true)]);
        $bytes = Excel::raw(new ContractorsExport, \Maatwebsite\Excel\Excel::XLSX);
        $this->assertStringStartsWith('PK', $bytes);
        $path = tempnam(sys_get_temp_dir(), 'contractor-export-');
        file_put_contents($path, $bytes);
        try {
            $sheet = IOFactory::load($path)->getActiveSheet();
            $this->assertSame($headers, $sheet->toArray()[0]);
            $this->assertCount(count($rows) + 1, $sheet->toArray());
            $sheet->getParent()->disconnectWorksheets();
        } finally {
            unlink($path);
        }
    }

    public function test_export_authorization_and_download(): void
    {
        $this->get('/dashboard/contractors/export')->assertRedirect();
        $this->actingAs(User::factory()->create(['role' => UserRole::Client, 'is_active' => true]))->get('/dashboard/contractors/export')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => UserRole::Manager, 'is_active' => true]))->get('/dashboard/contractors/export')->assertOk()->assertDownload();
    }
}
