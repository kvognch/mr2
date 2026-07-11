<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('geo_units')
            ->whereNotNull('admin_level')
            ->where('admin_level', '<=', 4)
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Intentionally empty: previous active statuses cannot be restored safely.
    }
};
