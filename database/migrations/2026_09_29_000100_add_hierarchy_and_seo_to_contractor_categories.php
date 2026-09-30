<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_categories', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->constrained('contractor_categories')->nullOnDelete();
            $table->text('intro_text')->nullable();
            $table->longText('seo_text')->nullable();
            $table->string('h1')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 1000)->nullable();
            $table->string('slug')->nullable()->unique();
        });

        $this->fillExistingSlugs('contractor_categories');
    }

    public function down(): void
    {
        Schema::table('contractor_categories', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['intro_text', 'seo_text', 'h1', 'meta_title', 'meta_description', 'slug']);
        });
    }

    private function fillExistingSlugs(string $tableName): void
    {
        DB::table($tableName)->orderBy('id')->get(['id', 'name'])->each(function (object $row) use ($tableName): void {
            $knownSlugs = [
                'Гарантирующий поставщик' => 'guaranteeing-supplier',
                'Ресурсо-снабжающая организация' => 'resource-supplying-organization',
                'Подрядчик' => 'contractors',
            ];
            $base = $knownSlugs[$row->name] ?? Str::slug(Str::transliterate((string) $row->name));
            $base = $base !== '' ? $base : 'category';
            $slug = $base;
            $suffix = 1;

            while (DB::table($tableName)->where('slug', $slug)->where('id', '<>', $row->id)->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            DB::table($tableName)->where('id', $row->id)->update(['slug' => $slug]);
        });
    }
};
