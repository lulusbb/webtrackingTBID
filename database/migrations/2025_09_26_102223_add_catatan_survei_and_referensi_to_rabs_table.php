<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            // Tambah "referensi" (kalau belum ada)
            if (!Schema::hasColumn('rabs', 'referensi')) {
                // letakkan setelah 'konsep_bangunan' (ubah posisi jika kolom tsb tidak ada)
                $table->text('referensi')->nullable()->after('konsep_bangunan');
            }

            // Tambah "catatan_survei" (kalau belum ada)
            if (!Schema::hasColumn('rabs', 'catatan_survei')) {
                // letakkan setelah 'gambar_kerja' (ubah posisi jika perlu)
                $table->text('catatan_survei')->nullable()->after('gambar_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('rabs', function (Blueprint $table) {
            if (Schema::hasColumn('rabs', 'catatan_survei')) {
                $table->dropColumn('catatan_survei');
            }
            if (Schema::hasColumn('rabs', 'referensi')) {
                $table->dropColumn('referensi');
            }
        });
    }
};
