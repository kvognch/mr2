<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const DOCUMENTS = [
        'offer' => 'Договор-оферта',
        'privacy-policy' => 'Политика обработки персональных данных',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::DOCUMENTS as $slug => $title) {
            $body = file_get_contents(database_path("data/legal/{$slug}.html"));

            if ($body === false || trim($body) === '') {
                throw new RuntimeException("Missing legal document: {$slug}");
            }

            DB::table('information_pages')->insertOrIgnore([
                'slug' => $slug,
                'title' => $title,
                'meta_title' => $title,
                'body' => $body,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $legal = DB::table('information_pages')->where('slug', 'legal')->lockForUpdate()->first();
        $body = (string) ($legal->body ?? '');
        $links = [];

        foreach (self::DOCUMENTS as $slug => $title) {
            $url = "/info/{$slug}";
            if (! preg_match('~href\s*=\s*(["\'])(?:https?://[^/]+)?'.preg_quote($url, '~').'/?\1~i', $body)) {
                $links[] = '<li><a href="'.$url.'">'.$title.'</a></li>';
            }
        }

        if ($links === []) {
            return;
        }

        $documents = '<ul>'.implode('', $links).'</ul>';
        if ($legal === null) {
            DB::table('information_pages')->insert([
                'slug' => 'legal',
                'title' => 'Правовые вопросы',
                'meta_title' => 'Правовые вопросы',
                'body' => $documents,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return;
        }

        if (trim(strip_tags($body)) === 'Страница находится в разработке') {
            $body = '';
        }

        DB::table('information_pages')->where('id', $legal->id)->update([
            'body' => $body === '' ? $documents : $body."\n".$documents,
            'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        // Keep published pages, which may have been edited in the admin panel.
    }
};
