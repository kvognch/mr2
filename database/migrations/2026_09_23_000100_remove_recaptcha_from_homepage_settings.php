<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // reCAPTCHA remains configurable in the admin panel; credentials now
        // come from environment-backed defaults instead of source code.
    }

    public function down(): void
    {
        // reCAPTCHA credentials are managed through environment variables.
    }
};
