<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('geo_units', function (Blueprint $table): void {
            $table->json('resource_schemes')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('geo_units', function (Blueprint $table): void {
            $table->dropColumn('resource_schemes');
        });
    }
};
