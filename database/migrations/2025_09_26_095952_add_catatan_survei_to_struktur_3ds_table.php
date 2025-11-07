<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struktur_3ds', function (Blueprint $table) {
            // tambahkan hanya kalau belum ada
            if (!Schema::hasColumn('struktur_3ds', 'catatan_survei')) {
                $table->text('catatan_survei')->nullable()->after('gambar_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struktur_3ds', function (Blueprint $table) {
            if (Schema::hasColumn('struktur_3ds', 'catatan_survei')) {
                $table->dropColumn('catatan_survei');
            }
        });
    }
};
