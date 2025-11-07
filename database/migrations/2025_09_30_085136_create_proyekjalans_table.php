<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proyekjalans', function (Blueprint $table) {
            $table->id();

            // relasi (opsional, aman bila tabel target sudah ada)
            $table->foreignId('klien_id')->nullable()
                  ->constrained('kliens')->nullOnDelete();
            $table->foreignId('mou_id')->nullable()
                  ->constrained('mous')->nullOnDelete();
            $table->foreignId('rab_id')->nullable()
                  ->constrained('rabs')->nullOnDelete();

            // data umum proyek
            $table->string('nama')->nullable();
            $table->string('kode_proyek')->nullable()->index();
            $table->string('kelas')->nullable();

            // alamat/lokasi
            $table->string('alamat_tinggal')->nullable();
            $table->string('lokasi_lahan')->nullable();

            // ukuran
            $table->decimal('luas_lahan', 10, 2)->nullable();
            $table->decimal('luas_bangunan', 10, 2)->nullable();

            // kebutuhan/konsep
            $table->text('kebutuhan_ruang')->nullable();
            $table->string('arah_mata_angin')->nullable();
            $table->string('batas_keliling')->nullable();
            $table->text('konsep_bangunan')->nullable();

            // angka & berkas
            $table->unsignedBigInteger('budget')->nullable();
            $table->string('lembar_diskusi')->nullable();
            $table->string('layout')->nullable();
            $table->string('desain_3d')->nullable();
            $table->string('rab_boq')->nullable();
            $table->string('gambar_kerja')->nullable();
            $table->text('lembar_survei')->nullable(); // PDF

            // lain-lain
            $table->text('keterangan')->nullable();
            $table->timestamp('tanggal_masuk')->nullable();

            // progres pengerjaan (0–100%)
            $table->unsignedTinyInteger('status_progres')
                  ->default(0)
                  ->comment('0–100 persen progres proyek');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proyekjalans');
    }
};
