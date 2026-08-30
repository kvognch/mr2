<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contractor_tariffs', function (Blueprint $table): void {
            $table->string('tariff_type')->default('connection')->after('contractor_id');
            $table->index(
                ['contractor_id', 'tariff_type', 'is_current'],
                'contractor_tariffs_type_current_index'
            );
        });

        DB::table('contractor_tariffs as tariffs')
            ->whereExists(function (Builder $query): void {
                $query
                    ->select(DB::raw('1'))
                    ->from('contractor_contractor_category as links')
                    ->join('contractor_categories as categories', 'categories.id', '=', 'links.contractor_category_id')
                    ->whereColumn('links.contractor_id', 'tariffs.contractor_id')
                    ->where('categories.name', 'Ресурсо-снабжающая организация');
            })
            ->whereNotExists(function (Builder $query): void {
                $query
                    ->select(DB::raw('1'))
                    ->from('contractor_contractor_category as links')
                    ->join('contractor_categories as categories', 'categories.id', '=', 'links.contractor_category_id')
                    ->whereColumn('links.contractor_id', 'tariffs.contractor_id')
                    ->where('categories.name', 'Гарантирующий поставщик');
            })
            ->update(['tariff_type' => 'sales']);
    }

    public function down(): void
    {
        Schema::table('contractor_tariffs', function (Blueprint $table): void {
            $table->dropIndex('contractor_tariffs_type_current_index');
            $table->dropColumn('tariff_type');
        });
    }
};
