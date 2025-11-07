<?php

// database/migrations/2025_09_22_100000_create_struktur3ds_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('struktur_3ds', function (Blueprint $t) {
            $t->id();

            // relasi opsional ke klien
            $t->foreignId('klien_id')
              ->nullable()
              ->constrained('kliens')
              ->nullOnDelete();

            // ===== blok kolom standar yang kamu inginkan =====
            $t->string('nama')->nullable();
            $t->string('kode_proyek')->index();
            $t->string('kelas', 10)->nullable();

            $t->string('alamat_tinggal')->nullable();
            $t->string('lokasi_lahan')->nullable();
            $t->decimal('luas_lahan', 10, 2)->nullable();
            $t->decimal('luas_bangunan', 10, 2)->nullable();

            $t->text('kebutuhan_ruang')->nullable();
            $t->string('arah_mata_angin')->nullable();
            $t->string('batas_keliling')->nullable();
            $t->text('konsep_bangunan')->nullable();

            $t->unsignedBigInteger('budget')->nullable();
            $t->string('lembar_diskusi')->nullable();
            $t->string('layout')->nullable();
            $t->string('desain_3d')->nullable();
            $t->string('rab_boq')->nullable();
            $t->string('gambar_kerja')->nullable();

            $t->text('keterangan')->nullable();
            $t->timestamp('tanggal_masuk')->nullable();

            $t->timestamps();
            $t->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('struktur_3ds');
    }
};

