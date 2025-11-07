<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            if (! Schema::hasColumn('mous', 'referensi')) {
                // Sesuaikan tipe; string cukup untuk path/file. Kalau mau panjang, bisa text().
                $table->string('referensi')->nullable()->after('gambar_kerja');
            }

            // Opsional: pastikan kolom lain yang kamu pakai juga ada
            if (! Schema::hasColumn('mous', 'lembar_survei')) {
                $table->string('lembar_survei')->nullable()->after('gambar_kerja');
            }
            if (! Schema::hasColumn('mous', 'catatan_survei')) {
                $table->text('catatan_survei')->nullable()->after('lembar_survei');
            }
            if (! Schema::hasColumn('mous', 'tahap_akhir_id')) {
                $table->unsignedBigInteger('tahap_akhir_id')->nullable()->after('klien_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            if (Schema::hasColumn('mous', 'referensi')) {
                $table->dropColumn('referensi');
            }
            // Jika di atas ikut menambah kolom lain dan ingin dibalik, boleh drop juga:
            // foreach (['lembar_survei','catatan_survei','tahap_akhir_id'] as $c) {
            //     if (Schema::hasColumn('mous', $c)) $table->dropColumn($c);
            // }
        });
    }
};
