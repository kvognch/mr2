<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('editorial_rubrics', function (Blueprint $t) {
            $t->id();
            $t->string('kind');
            $t->string('name');
            $t->string('slug');
            $t->integer('sort_order')->default(0);
            $t->boolean('is_visible')->default(true);
            $t->timestamps();
            $t->unique(['kind', 'slug']);
        });
        Schema::create('publications', function (Blueprint $t) {
            $t->id();
            $t->string('kind');
            $t->string('title');
            $t->string('slug');
            $t->foreignId('rubric_id')->nullable()->constrained('editorial_rubrics')->nullOnDelete();
            $t->string('status')->default('draft');
            $t->text('excerpt')->nullable();
            $t->string('image')->nullable();
            $t->text('body')->nullable();
            $t->timestamp('published_at')->nullable();
            $t->timestamp('content_updated_at')->nullable();
            $t->boolean('is_federal')->default(false);
            $t->string('source_name')->nullable();
            $t->text('source_url')->nullable();
            $t->text('source_document')->nullable();
            $t->date('source_checked_at')->nullable();
            $t->text('audience')->nullable();
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->json('cta')->nullable();
            $t->timestamps();
            $t->unique(['kind', 'slug']);
            $t->index(['kind', 'status', 'published_at']);
        });
        foreach (['resource_type' => 'resource_types', 'geo_unit' => 'geo_units', 'contractor' => 'contractors'] as $key => $target) {
            Schema::create('publication_'.$key, function (Blueprint $t) use ($key, $target) {
                $t->foreignId('publication_id')->constrained('publications')->cascadeOnDelete();
                $t->foreignId($key.'_id')->constrained($target)->cascadeOnDelete();
                $t->primary(['publication_id', $key.'_id']);
            });
        }
        Schema::create('publication_related', function (Blueprint $t) {
            $t->foreignId('news_id')->constrained('publications')->cascadeOnDelete();
            $t->foreignId('article_id')->constrained('publications')->cascadeOnDelete();
            $t->integer('sort_order')->default(0);
            $t->primary(['news_id', 'article_id']);
        });
        foreach (['blog' => ['Подключение' => 'connection', 'Документы' => 'documents', 'Проектирование и строительство' => 'design-construction'], 'news' => ['Правила и законы' => 'laws', 'Тарифы' => 'tariffs', 'Новости компаний' => 'companies', 'Инфраструктура' => 'infrastructure']] as $kind => $rubrics) {
            foreach ($rubrics as $name => $slug) {
                DB::table('editorial_rubrics')->insert(['kind' => $kind, 'name' => $name, 'slug' => $slug, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        foreach (['publication_related', 'publication_contractor', 'publication_geo_unit', 'publication_resource_type', 'publications', 'editorial_rubrics'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
