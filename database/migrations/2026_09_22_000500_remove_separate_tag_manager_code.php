<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $storedSeo = DB::table('settings')->where('key', 'seo')->value('value');
        $seo = is_string($storedSeo) ? json_decode($storedSeo, true) : null;

        if (! is_array($seo) || ! isset($seo['tracking']) || ! is_array($seo['tracking'])) {
            return;
        }

        if (! array_key_exists('yandex_tag_manager', $seo['tracking'])) {
            return;
        }

        unset($seo['tracking']['yandex_tag_manager']);

        DB::table('settings')
            ->where('key', 'seo')
            ->update([
                'value' => json_encode($seo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        // The separate Tag Manager code field is intentionally not restored.
    }
};
