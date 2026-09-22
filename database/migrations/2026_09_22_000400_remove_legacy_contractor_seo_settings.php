<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $storedSeo = DB::table('settings')->where('key', 'seo')->value('value');
        $seo = is_string($storedSeo) ? json_decode($storedSeo, true) : null;

        if (! is_array($seo) || ! array_key_exists('contractor', $seo)) {
            return;
        }

        unset($seo['contractor']);

        DB::table('settings')
            ->where('key', 'seo')
            ->update([
                'value' => json_encode($seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // Legacy organization SEO settings are intentionally not restored.
    }
};
