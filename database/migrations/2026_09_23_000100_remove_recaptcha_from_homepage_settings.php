<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')
            ->where('key', 'homepage.google_recaptcha')
            ->delete();
    }

    public function down(): void
    {
        // reCAPTCHA credentials are managed through environment variables.
    }
};
