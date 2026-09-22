<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $storedSeo = DB::table('settings')->where('key', 'seo')->value('value');
        $storedMeta = DB::table('settings')->where('key', 'homepage.meta')->value('value');
        $storedOrganizations = DB::table('settings')->where('key', 'seo.organizations')->value('value');

        $seo = is_string($storedSeo) ? json_decode($storedSeo, true) : null;
        $meta = is_string($storedMeta) ? json_decode($storedMeta, true) : null;
        $organizations = is_string($storedOrganizations) ? json_decode($storedOrganizations, true) : null;
        $payload = is_array($seo) ? $seo : [];

        if (is_array($meta)) {
            foreach (['home', 'search', 'contractor'] as $section) {
                if (! isset($meta[$section]) || ! is_array($meta[$section])) {
                    continue;
                }

                $payload[$section] = array_replace_recursive($meta[$section], $payload[$section] ?? []);
            }
        }

        if (is_array($organizations)) {
            $payload['organizations'] = array_replace_recursive(
                $organizations,
                $payload['organizations'] ?? [],
            );
        }

        if ($payload !== []) {
            DB::table('settings')->updateOrInsert(
                ['key' => 'seo'],
                [
                    'value' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
        }

        DB::table('settings')
            ->whereIn('key', ['homepage.meta', 'seo.organizations'])
            ->delete();
    }

    public function down(): void
    {
        // SEO values stay in the unified key to avoid destructive data rollback.
    }
};
