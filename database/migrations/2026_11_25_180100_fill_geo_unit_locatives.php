<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Match names, not environment-specific IDs. Never replace manual values.
        $forms = json_decode(file_get_contents(database_path('data/geo_unit_locatives.json')), true, 512, JSON_THROW_ON_ERROR);

        DB::table('geo_units')
            ->select(['id', 'name'])
            ->whereNull('name_locative')
            ->orderBy('id')
            ->chunkById(100, function ($units) use ($forms): void {
                foreach ($units as $unit) {
                    if (isset($forms[$unit->name])) {
                        DB::table('geo_units')
                            ->where('id', $unit->id)
                            ->where('name', $unit->name)
                            ->whereNull('name_locative')
                            ->update(['name_locative' => $forms[$unit->name]]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Do not erase phrases that may have been edited after the backfill.
    }
};
