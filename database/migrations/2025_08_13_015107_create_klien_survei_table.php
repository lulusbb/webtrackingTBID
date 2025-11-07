<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_klien_survei_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('klien_survei', function (Blueprint $table) {
            $table->id();
            $table->foreignId('klien_id')->unique()->constrained('kliens')->cascadeOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            // tambahkan kolom lain (jadwal_survei, catatan, dsb) kalau perlu
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('klien_survei');
    }
};
