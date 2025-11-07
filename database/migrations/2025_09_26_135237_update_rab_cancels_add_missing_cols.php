<?php

// database/migrations/2025_09_26_120000_update_rab_cancels_add_missing_cols.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('rab_cancels')) return;

        if (! Schema::hasColumn('rab_cancels','klien_id')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->unsignedBigInteger('klien_id')->nullable()->after('rab_id');
            });
        }
        if (! Schema::hasColumn('rab_cancels','nama')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->string('nama')->nullable()->after('klien_id');
            });
        }
        if (! Schema::hasColumn('rab_cancels','kode_proyek')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->string('kode_proyek')->nullable()->after('nama');
            });
        }
        if (! Schema::hasColumn('rab_cancels','lokasi_lahan')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->string('lokasi_lahan')->nullable()->after('kode_proyek');
            });
        }
        if (! Schema::hasColumn('rab_cancels','alasan_cancel')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->text('alasan_cancel')->nullable()->after('lokasi_lahan');
            });
        }
        if (! Schema::hasColumn('rab_cancels','canceled_at')) {
            Schema::table('rab_cancels', function (Blueprint $table) {
                $table->timestamp('canceled_at')->nullable()->after('alasan_cancel');
            });
        }
    }

    public function down(): void
    {
        // optional: kosongkan saja atau drop kolom di sini jika perlu rollback
    }
};
