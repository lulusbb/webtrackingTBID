<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('klienfixsurvei', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('klien_id')->index();

            // ===== snapshot kolom dari tabel kliens =====
            $table->string('nama')->nullable();
            $table->string('lokasi_lahan')->nullable();
            $table->string('luas_lahan')->nullable();
            $table->string('luas_bangunan')->nullable();
            $table->string('kebutuhan_ruang')->nullable();
            $table->string('sertifikat')->nullable();
            $table->string('arah_mata_angin')->nullable();
            $table->string('batas_keliling')->nullable();
            $table->string('foto_eksisting')->nullable();
            $table->string('konsep_bangunan')->nullable();
            $table->string('referensi')->nullable();
            $table->bigInteger('budget')->nullable();
            $table->string('share_lokasi')->nullable();
            $table->string('biaya_survei')->nullable();
            $table->string('hoby')->nullable();
            $table->string('aktivitas')->nullable();
            $table->string('prioritas_ruang')->nullable();
            $table->string('kendaraan')->nullable();
            $table->string('estimasi_start')->nullable();
            $table->string('target_user_kos')->nullable();
            $table->string('fasilitas_kos')->nullable();
            $table->string('layout')->nullable();
            $table->string('desain_3d')->nullable();
            $table->string('rab_boq')->nullable();
            $table->string('gambar_kerja')->nullable();
            $table->date('tanggal_masuk')->nullable();
            $table->string('email')->nullable();
            $table->string('alamat_tinggal')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('kode_proyek')->nullable();
            $table->string('kelas')->nullable();
            $table->text('keterangan')->nullable();

            // ===== penjadwalan =====
            $table->dateTime('schedule_at')->nullable();  // tanggal + jam survei (WIB)
            $table->unsignedBigInteger('scheduled_by')->nullable();

            $table->timestamps();
        });

        // (Opsional) tambahkan schedule_at ke survey_requests jika belum ada
        if (!Schema::hasColumn('survey_requests','schedule_at')) {
            Schema::table('survey_requests', function (Blueprint $t) {
                $t->dateTime('schedule_at')->nullable()->after('approved_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('klienfixsurvei');

        if (Schema::hasColumn('survey_requests','schedule_at')) {
            Schema::table('survey_requests', fn (Blueprint $t) => $t->dropColumn('schedule_at'));
        }
    }
};
