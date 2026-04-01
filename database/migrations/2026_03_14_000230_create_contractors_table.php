<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contractors', function (Blueprint $table) {
            $table->id();
            $table->string('short_name');
            $table->string('full_name')->nullable();
            $table->json('business_segments')->nullable();
            $table->string('website')->nullable();
            $table->string('social_telegram')->nullable();
            $table->string('social_vk')->nullable();
            $table->string('social_whatsapp')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('response_time')->nullable();
            $table->string('work_volume')->nullable();
            $table->boolean('smr_has_sro')->default(false);
            $table->boolean('pir_has_sro')->default(false);
            $table->string('ogrn')->nullable();
            $table->string('inn')->nullable();
            $table->string('kpp')->nullable();
            $table->date('registration_date')->nullable();
            $table->string('legal_address')->nullable();
            $table->json('branch_contacts')->nullable();
            $table->text('additional_info')->nullable();
            $table->foreignId('rating_id')->nullable()->constrained('ratings')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->index();
            $table->foreignId('owner_id')->nullable()->constrained('users')->nullOnDelete()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contractors');
    }
};
