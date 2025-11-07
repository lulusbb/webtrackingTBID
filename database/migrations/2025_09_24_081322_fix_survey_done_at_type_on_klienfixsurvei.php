<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Ubah TIMESTAMP -> DATETIME NULL DEFAULT NULL
        DB::statement("
            ALTER TABLE klienfixsurvei
            MODIFY survey_done_at DATETIME NULL DEFAULT NULL
        ");
    }

    public function down(): void
    {
        // Balik lagi kalau perlu (opsional)
        DB::statement("
            ALTER TABLE klienfixsurvei
            MODIFY survey_done_at TIMESTAMP NULL DEFAULT NULL
        ");
    }
};
