<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('geo_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('geo_units')->nullOnDelete();
            $table->string('source', 32)->default('osm');
            $table->string('source_id');
            $table->string('parent_source_id')->nullable();
            $table->string('name');
            $table->string('normalized_name')->index();
            $table->unsignedSmallInteger('admin_level')->nullable()->index();
            $table->string('level', 32)->default('other')->index();
            $table->string('boundary', 64)->nullable()->index();
            $table->json('geometry_osm')->nullable();
            $table->json('geometry_yandex')->nullable();
            $table->decimal('center_lat', 10, 7)->nullable();
            $table->decimal('center_lon', 10, 7)->nullable();
            $table->decimal('bbox_min_lat', 10, 7)->nullable();
            $table->decimal('bbox_min_lon', 10, 7)->nullable();
            $table->decimal('bbox_max_lat', 10, 7)->nullable();
            $table->decimal('bbox_max_lon', 10, 7)->nullable();
            $table->boolean('is_active')->default(false)->index();
            $table->json('properties')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['source', 'source_id']);
            $table->index(['source', 'parent_source_id']);
            $table->index(['parent_id', 'admin_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geo_units');
    }
};
