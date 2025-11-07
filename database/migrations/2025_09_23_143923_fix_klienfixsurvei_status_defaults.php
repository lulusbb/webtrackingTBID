<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Pastikan kolom status_survei ada dan default "Belum diSurvei"
        // Jika kolom belum ada, tambahkan manual dulu (atau pastikan migration sebelumnya sudah menambah)
        // Di sini kita MODIFIKASI dengan SQL murni (tanpa DBAL).
        DB::statement("
            ALTER TABLE klienfixsurvei
            MODIFY COLUMN status_survei VARCHAR(50) NOT NULL DEFAULT 'Belum diSurvei'
        ");

        // Pastikan survey_done_at bisa NULL dan default NULL (bukan CURRENT_TIMESTAMP)
        DB::statement("
            ALTER TABLE klienfixsurvei
            MODIFY COLUMN survey_done_at TIMESTAMP NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        // Biarkan kosong (no-op). Kita tidak ingin rollback ke kondisi salah.
    }
};
