<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('meps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klien_id')->nullable()->index();
            $table->unsignedBigInteger('klienfixsurvei_id')->nullable()->index();
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
            $table->string('konsep_bangunan')->nullable();
            $table->text('referensi')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->string('lembar_diskusi')->nullable();
            $table->string('layout')->nullable();
            $table->string('desain_3d')->nullable();
            $table->string('rab_boq')->nullable();
            $table->string('lembar_survei')->nullable();
            $table->text('catatan_survei')->nullable();
            $table->string('gambar_kerja')->nullable();
            $table->string('status_denah')->nullable()->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exteriors');
    }
};
