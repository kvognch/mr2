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
        Schema::table('resource_types', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->constrained('resource_types')->nullOnDelete();
            $table->text('intro_text')->nullable();
            $table->longText('seo_text')->nullable();
            $table->string('h1')->nullable();
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 1000)->nullable();
            $table->string('slug')->nullable()->unique();
        });

        $this->fillExistingSlugs('resource_types');
    }

    public function down(): void
    {
        Schema::table('resource_types', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['intro_text', 'seo_text', 'h1', 'meta_title', 'meta_description', 'slug']);
        });
    }

    private function fillExistingSlugs(string $tableName): void
    {
        DB::table($tableName)->orderBy('id')->get(['id', 'name'])->each(function (object $row) use ($tableName): void {
            $knownSlugs = [
                'Газоснабжение' => 'gas-supply',
                'Наружная канализация' => 'external-sewerage',
                'Наружный водопровод' => 'external-water-supply',
                'Теплоснабжение' => 'heat-supply',
                'Электроснабжение' => 'electricity',
            ];
            $base = $knownSlugs[$row->name] ?? Str::slug(Str::transliterate((string) $row->name));
            $base = $base !== '' ? $base : 'direction';
            $slug = $base;
            $suffix = 1;

            while (DB::table($tableName)->where('slug', $slug)->where('id', '<>', $row->id)->exists()) {
                $slug = $base.'-'.$suffix++;
            }

            DB::table($tableName)->where('id', $row->id)->update(['slug' => $slug]);
        });
    }
};
