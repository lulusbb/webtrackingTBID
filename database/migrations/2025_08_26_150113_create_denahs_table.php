<?php

// database/migrations/xxxx_xx_xx_xxxxxx_create_denahs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('denahs', function (Blueprint $table) {
            $table->id();

            // relasi sumber data
            $table->unsignedBigInteger('klien_id')->nullable()->index();
            $table->unsignedBigInteger('klienfixsurvei_id')->nullable()->unique();

            // snapshot dasar yang berguna untuk tahap denah+moodboard
            $table->string('nama')->nullable();
            $table->string('kode_proyek')->nullable();
            $table->string('kelas')->nullable();

            $table->string('lokasi_lahan')->nullable();
            $table->string('alamat_tinggal')->nullable();
            $table->decimal('luas_lahan', 10, 2)->nullable();
            $table->decimal('luas_bangunan', 10, 2)->nullable();
            $table->text('kebutuhan_ruang')->nullable();
            $table->string('arah_mata_angin')->nullable();
            $table->string('batas_keliling')->nullable();

            $table->text('konsep_bangunan')->nullable();
            $table->text('referensi')->nullable();
            $table->bigInteger('budget')->nullable();

            // file yang sering dipakai di tahap awal
            $table->string('lembar_diskusi')->nullable();
            $table->string('layout')->nullable();
            $table->string('desain_3d')->nullable();
            $table->string('rab_boq')->nullable();
            $table->string('gambar_kerja')->nullable();

            // status/progress denah
            $table->string('status_denah')->default('draft'); // draft | proses | final

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denahs');
    }
};
