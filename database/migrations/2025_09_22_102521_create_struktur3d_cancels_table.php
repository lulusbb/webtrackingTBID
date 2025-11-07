<?php

// database/migrations/2025_09_22_102520_create_struktur3d_cancels_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('struktur_3d_cancels', function (Blueprint $t) {
            $t->id();

            // FK WAJIB nullable jika pakai on delete set null
            $t->foreignId('struktur3d_id')
              ->nullable()
              ->constrained('struktur_3ds')
              ->nullOnDelete()
              ->cascadeOnUpdate();

            $t->foreignId('klien_id')
              ->nullable()
              ->constrained('kliens')
              ->nullOnDelete();

            // ringkas data penting + meta cancel
            $t->string('nama')->nullable();
            $t->string('kode_proyek')->nullable();
            $t->string('lokasi_lahan')->nullable();

            $t->text('alasan_cancel')->nullable();
            $t->timestamp('canceled_at')->nullable();

            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_3d_cancels');
    }
};

