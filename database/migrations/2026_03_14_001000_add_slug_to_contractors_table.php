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
        Schema::table('contractors', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('short_name')->unique();
        });

        $existing = DB::table('contractors')->select('id', 'short_name')->orderBy('id')->get();

        foreach ($existing as $contractor) {
            $base = Str::slug(Str::transliterate((string) $contractor->short_name));
            if ($base === '') {
                $base = 'agent';
            }

            $slug = $base;
            $suffix = 1;

            while (DB::table('contractors')->where('slug', $slug)->where('id', '!=', $contractor->id)->exists()) {
                $slug = $base . '-' . $suffix;
                $suffix++;
            }

            DB::table('contractors')->where('id', $contractor->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('contractors', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
