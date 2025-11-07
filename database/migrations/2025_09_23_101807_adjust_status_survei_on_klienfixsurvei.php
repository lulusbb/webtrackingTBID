<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Pastikan kolom status_survei ADA
        if (!Schema::hasColumn('klienfixsurvei', 'status_survei')) {
            Schema::table('klienfixsurvei', function (Blueprint $t) {
                // tanpa "after" agar tidak bergantung kolom lain
                $t->string('status_survei', 20)->default('Belum diSurvei');
            });
        }

        // 2) Paksa DEFAULT = 'Belum diSurvei' (tanpa DBAL, pakai statement)
        try {
            DB::statement("
                ALTER TABLE klienfixsurvei
                MODIFY status_survei VARCHAR(20) NOT NULL DEFAULT 'Belum diSurvei'
            ");
        } catch (\Throwable $e) {
            // abaikan jika engine sudah cocok
        }

        // 3) Tambah kolom survey_done_at bila belum ada
        if (!Schema::hasColumn('klienfixsurvei', 'survey_done_at')) {
            Schema::table('klienfixsurvei', function (Blueprint $t) {
                $t->timestamp('survey_done_at')->nullable();
            });
        }

        // 4) Hotfix data lama: NULL/kosong -> Belum diSurvei
        DB::statement("
            UPDATE klienfixsurvei
               SET status_survei = 'Belum diSurvei'
             WHERE status_survei IS NULL OR status_survei = ''
        ");

        // 5) Jika ada baris 'Sudah diSurvei' tapi belum ada waktu selesai -> kembalikan
        DB::statement("
            UPDATE klienfixsurvei
               SET status_survei = 'Belum diSurvei'
             WHERE status_survei = 'Sudah diSurvei' AND survey_done_at IS NULL
        ");
    }

    public function down(): void
    {
        // optional: drop kolom tambahan
        if (Schema::hasColumn('klienfixsurvei', 'survey_done_at')) {
            Schema::table('klienfixsurvei', function (Blueprint $t) {
                $t->dropColumn('survey_done_at');
            });
        }
        // (biasanya tidak perlu drop status_survei, tapi kalau mau:)
        // if (Schema::hasColumn('klienfixsurvei', 'status_survei')) {
        //     Schema::table('klienfixsurvei', function (Blueprint $t) {
        //         $t->dropColumn('status_survei');
        //     });
        // }
    }
};
