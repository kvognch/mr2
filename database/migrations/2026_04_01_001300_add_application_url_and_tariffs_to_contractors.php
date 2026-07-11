<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractors', function (Blueprint $table): void {
            $table->string('application_url')->nullable()->after('website');
        });

        Schema::create('contractor_tariffs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('contractor_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->nullable();
            $table->boolean('is_current')->default(true)->index();
            $table->timestamps();

            $table->index(['contractor_id', 'is_current']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractor_tariffs');

        Schema::table('contractors', function (Blueprint $table): void {
            $table->dropColumn('application_url');
        });
    }
};
