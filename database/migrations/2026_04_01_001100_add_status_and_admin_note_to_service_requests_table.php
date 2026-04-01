<?php

use App\Enums\ServiceRequestStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_requests', function (Blueprint $table): void {
            $table->string('status', 32)
                ->default(ServiceRequestStatus::Pending->value)
                ->after('comment');
            $table->text('admin_note')
                ->nullable()
                ->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table): void {
            $table->dropColumn(['status', 'admin_note']);
        });
    }
};
