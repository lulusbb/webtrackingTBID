<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('skemas', function (Blueprint $table) {
            // Tambah "referensi" sesudah "konsep_bangunan" bila belum ada
            if (!Schema::hasColumn('skemas', 'referensi')) {
                $table->text('referensi')->nullable()->after('konsep_bangunan');
            }

            // Tambah "catatan_survei" sesudah "gambar_kerja" bila belum ada
            if (!Schema::hasColumn('skemas', 'catatan_survei')) {
                $table->text('catatan_survei')->nullable()->after('gambar_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skemas', function (Blueprint $table) {
            if (Schema::hasColumn('skemas', 'catatan_survei')) {
                $table->dropColumn('catatan_survei');
            }
            if (Schema::hasColumn('skemas', 'referensi')) {
                $table->dropColumn('referensi');
            }
        });
    }
};
