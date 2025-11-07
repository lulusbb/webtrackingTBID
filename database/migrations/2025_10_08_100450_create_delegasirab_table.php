<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('delegasirab', function (Blueprint $table) {
            $table->id();

            // Relasi (optional, dibiarkan nullable tanpa constraint biar aman)
            $table->unsignedBigInteger('klien_id')->nullable();
            $table->unsignedBigInteger('klienfixsurvei_id')->nullable();

            // Identitas / proyek
            $table->string('nama')->nullable();
            $table->string('kode_proyek')->nullable();
            $table->string('kelas')->nullable();

            // Lokasi & ukuran
            $table->string('lokasi_lahan')->nullable();
            $table->string('alamat_tinggal')->nullable();
            $table->string('luas_lahan')->nullable();
            $table->string('luas_bangunan')->nullable();
            $table->text('kebutuhan_ruang')->nullable();

            // Detail lahan
            $table->string('sertifikat')->nullable();
            $table->string('arah_mata_angin')->nullable();
            $table->text('batas_keliling')->nullable();

            // Referensi / lampiran
            $table->string('foto_eksisting')->nullable();
            $table->text('konsep_bangunan')->nullable();
            $table->text('referensi')->nullable();

            // Finansial
            $table->decimal('budget', 15, 2)->nullable();
            $table->string('share_lokasi')->nullable();
            $table->decimal('biaya_survei', 15, 2)->nullable();

            // Preferensi
            $table->string('hoby')->nullable();
            $table->string('aktivitas')->nullable();
            $table->text('prioritas_ruang')->nullable();
            $table->string('kendaraan')->nullable();

            // Estimasi & fasilitas
            $table->date('estimasi_start')->nullable();
            $table->string('target_user_kos')->nullable();
            $table->text('fasilitas_kos')->nullable();

            // Berkas
            $table->string('layout')->nullable();
            $table->string('desain_3d')->nullable();
            $table->string('rab_boq')->nullable();
            $table->string('gambar_kerja')->nullable();

            // Kontak & waktu
            $table->dateTime('tanggal_masuk')->nullable();
            $table->string('email')->nullable();
            $table->string('no_hp')->nullable();

            // Lain-lain
            $table->text('keterangan')->nullable();
            $table->string('lembar_diskusi')->nullable();
            $table->string('lembar_survei')->nullable();
            $table->text('catatan_survei')->nullable();
            $table->string('status_mep')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegasirab');
    }
};
