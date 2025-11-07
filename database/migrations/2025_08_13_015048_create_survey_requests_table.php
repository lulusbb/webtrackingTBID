<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_survey_requests_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('survey_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')->constrained('kliens')->cascadeOnDelete();
            $table->enum('status', ['pending','accepted','rejected'])->default('pending');
            $table->foreignId('sent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['klien_id','status']); // mencegah pending ganda untuk klien sama
        });
    }
    public function down(): void {
        Schema::dropIfExists('survey_requests');
    }
};
