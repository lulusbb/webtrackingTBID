<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kliens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('lokasi_lahan')->nullable();
            $table->float('luas_lahan')->nullable();
            $table->float('luas_bangunan')->nullable();
            $table->text('kebutuhan_ruang')->nullable();
            $table->string('sertifikat')->nullable(); // file
            $table->string('arah_mata_angin')->nullable();
            $table->text('batas_keliling')->nullable();
            $table->string('foto_eksisting')->nullable(); // file
            $table->text('konsep_bangunan')->nullable();
            $table->string('referensi')->nullable(); // file
            $table->string('budget')->nullable();
            $table->string('share_lokasi')->nullable();
            $table->string('biaya_survei')->nullable();
            $table->string('hoby')->nullable();
            $table->text('aktivitas')->nullable();
            $table->text('prioritas_ruang')->nullable();
            $table->string('kendaraan')->nullable();
            $table->date('estimasi_start')->nullable();
            $table->string('target_user_kos')->nullable();
            $table->text('fasilitas_kos')->nullable();
            $table->string('layout')->nullable(); // file
            $table->string('desain_3d')->nullable(); // file
            $table->string('rab_boq')->nullable(); // file
            $table->string('gambar_kerja')->nullable(); // file
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kliens');
    }
};
