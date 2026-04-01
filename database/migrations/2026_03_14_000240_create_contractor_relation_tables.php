<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractor_contractor_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnDelete();
            $table->foreignId('contractor_category_id')->constrained('contractor_categories')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contractor_id', 'contractor_category_id'], 'contractor_category_unique');
        });

        Schema::create('contractor_smr_resource_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->constrained('resource_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contractor_id', 'resource_type_id'], 'contractor_smr_resource_unique');
        });

        Schema::create('contractor_pir_resource_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnDelete();
            $table->foreignId('resource_type_id')->constrained('resource_types')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contractor_id', 'resource_type_id'], 'contractor_pir_resource_unique');
        });

        Schema::create('contractor_geo_unit', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contractor_id')->constrained('contractors')->cascadeOnDelete();
            $table->foreignId('geo_unit_id')->constrained('geo_units')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['contractor_id', 'geo_unit_id'], 'contractor_geo_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_geo_unit');
        Schema::dropIfExists('contractor_pir_resource_type');
        Schema::dropIfExists('contractor_smr_resource_type');
        Schema::dropIfExists('contractor_contractor_category');
    }
};
