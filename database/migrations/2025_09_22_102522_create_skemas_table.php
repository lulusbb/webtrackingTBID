<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('skemas', function (Blueprint $t) {
            $t->id();
            $t->foreignId('klien_id')->nullable()->constrained('kliens')->nullOnDelete();
            // OPSIONAL: relasi asal dari struktur_3ds
            $t->foreignId('struktur3d_id')->nullable()->constrained('struktur_3ds')->nullOnDelete();

            // -- blok yang sama --
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
    public function down(): void {
        Schema::dropIfExists('skemas');
    }
};


