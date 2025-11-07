<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pastikan sumber ada
        if (!Schema::hasTable('proyekjalans')) {
            throw new RuntimeException('Tabel sumber "proyekjalans" tidak ditemukan.');
        }

        // Clone struktur tabel proyekjalans -> proyekselesaii (MySQL)
        DB::statement('CREATE TABLE IF NOT EXISTS proyekselesaii LIKE proyekjalans');

        // Tambahan kolom tracking (abaikan jika sudah ada)
        if (!Schema::hasColumn('proyekselesaii', 'tanggal_selesai')) {
            DB::statement('ALTER TABLE proyekselesaii ADD COLUMN tanggal_selesai DATETIME NULL AFTER tanggal_mulai');
        }
        if (!Schema::hasColumn('proyekselesaii', 'moved_from_id')) {
            DB::statement('ALTER TABLE proyekselesaii ADD COLUMN moved_from_id BIGINT NULL');
        }
        if (!Schema::hasColumn('proyekselesaii', 'moved_at')) {
            DB::statement('ALTER TABLE proyekselesaii ADD COLUMN moved_at DATETIME NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('proyekselesaii');
    }
};
