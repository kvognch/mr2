<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('information_pages', function (Blueprint $table): void {
            $table->boolean('use_rich_editor')->default(true)->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('information_pages', function (Blueprint $table): void {
            $table->dropColumn('use_rich_editor');
        });
    }
};
