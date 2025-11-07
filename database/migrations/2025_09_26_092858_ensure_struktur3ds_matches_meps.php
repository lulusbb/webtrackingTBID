<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Buat tabel jika belum ada
        if (! Schema::hasTable('struktur_3ds')) {
            Schema::create('struktur_3ds', function (Blueprint $t) {
                $t->id();

                $t->unsignedBigInteger('klien_id')->nullable()->index();
                $t->unsignedBigInteger('klienfixsurvei_id')->nullable()->index();

                $t->string('nama')->nullable();
                $t->string('kode_proyek')->nullable();
                $t->string('kelas')->nullable();

                $t->string('lokasi_lahan')->nullable();
                $t->string('alamat_tinggal')->nullable();

                $t->decimal('luas_lahan', 10, 2)->nullable();
                $t->decimal('luas_bangunan', 10, 2)->nullable();

                $t->text('kebutuhan_ruang')->nullable();
                $t->string('arah_mata_angin')->nullable();
                $t->string('batas_keliling')->nullable();
                $t->text('konsep_bangunan')->nullable();
                $t->text('referensi')->nullable();

                $t->bigInteger('budget')->nullable();

                $t->string('lembar_diskusi')->nullable();
                $t->string('layout')->nullable();
                $t->string('desain_3d')->nullable();
                $t->string('rab_boq')->nullable();
                $t->string('gambar_kerja')->nullable();

                $t->text('keterangan')->nullable();
                $t->timestamp('tanggal_masuk')->nullable();

                // status default seperti di MEP
                $t->string('status_denah')->default('draft');

                $t->timestamps();
                $t->softDeletes();
            });

            return;
        }

        // Kalau tabel sudah ada: tambahkan kolom yang belum ada
        Schema::table('struktur_3ds', function (Blueprint $t) {
            if (! Schema::hasColumn('struktur_3ds', 'klienfixsurvei_id')) {
                $t->unsignedBigInteger('klienfixsurvei_id')->nullable()->after('klien_id')->index();
            }
            if (! Schema::hasColumn('struktur_3ds', 'referensi')) {
                $t->text('referensi')->nullable()->after('konsep_bangunan');
            }
            if (! Schema::hasColumn('struktur_3ds', 'status_denah')) {
                $t->string('status_denah')->default('draft')->after('keterangan');
            }
        });

        // Pastikan beberapa kolom nullable/tipe sesuai tanpa Doctrine
        try { DB::statement("ALTER TABLE struktur_3ds MODIFY kode_proyek VARCHAR(255) NULL"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE struktur_3ds MODIFY luas_lahan DECIMAL(10,2) NULL"); } catch (\Throwable $e) {}
        try { DB::statement("ALTER TABLE struktur_3ds MODIFY luas_bangunan DECIMAL(10,2) NULL"); } catch (\Throwable $e) {}
    }

    public function down(): void
    {
        // Aman: tidak perlu drop tabel saat rollback
        // Jika ingin bisa rollback penuh:
        // Schema::dropIfExists('struktur_3ds');
    }
};
