<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Pastikan kolom ADA
        if (!Schema::hasColumn('klienfixsurvei', 'status_survei')) {
            Schema::table('klienfixsurvei', function (Blueprint $t) {
                $t->string('status_survei', 30)->default('Belum diSurvei');
            });
        }
        if (!Schema::hasColumn('klienfixsurvei', 'survey_done_at')) {
            Schema::table('klienfixsurvei', function (Blueprint $t) {
                $t->timestamp('survey_done_at')->nullable();
            });
        }

        // 2) Paksa DEFAULT & NULLABILITY (tanpa DBAL)
        //    - status_survei default 'Belum diSurvei'
        //    - survey_done_at benar2 nullable
        try {
            DB::statement("ALTER TABLE klienfixsurvei 
                MODIFY status_survei VARCHAR(30) NOT NULL DEFAULT 'Belum diSurvei'");
        } catch (\Throwable $e) {
            // abaikan jika engine/versi sudah cocok
        }

        try {
            DB::statement("ALTER TABLE klienfixsurvei 
                MODIFY survey_done_at TIMESTAMP NULL DEFAULT NULL");
        } catch (\Throwable $e) {}

        // 3) Normalisasi data lama
        //    - jika survey_done_at is NULL => Belum diSurvei
        //    - jika survey_done_at terisi    => Sudah diSurvei
        DB::statement("UPDATE klienfixsurvei 
                       SET status_survei = 'Belum diSurvei' 
                       WHERE survey_done_at IS NULL");

        DB::statement("UPDATE klienfixsurvei 
                       SET status_survei = 'Sudah diSurvei' 
                       WHERE survey_done_at IS NOT NULL");

        // 4) (Opsional) Kunci unik supaya 1 SurveyRequest hanya punya 1 jadwal aktif
        //    Jalankan hanya jika kolomnya ada. Bisa gagal jika sudah ada duplikat—bersihkan dulu kalau perlu.
        if (Schema::hasColumn('klienfixsurvei', 'survey_request_id')) {
            try {
                DB::statement("
                    ALTER TABLE klienfixsurvei
                    ADD UNIQUE KEY uniq_klienfixsurvei_sreq (survey_request_id)
                ");
            } catch (\Throwable $e) {
                // kemungkinan index sudah ada / ada duplikat; boleh diabaikan atau dibereskan manual
            }
        }
    }

    public function down(): void
    {
        // Revert ringan: hilangkan default (opsional)
        try {
            DB::statement("ALTER TABLE klienfixsurvei 
                MODIFY status_survei VARCHAR(30) NOT NULL");
        } catch (\Throwable $e) {}

        // Hapus index unik (opsional)
        try {
            DB::statement("
                ALTER TABLE klienfixsurvei
                DROP INDEX uniq_klienfixsurvei_sreq
            ");
        } catch (\Throwable $e) {}
    }
};
