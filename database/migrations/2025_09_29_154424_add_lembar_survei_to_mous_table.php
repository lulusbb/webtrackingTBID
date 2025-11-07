<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom 'lembar_survei' ke tabel 'mous'
        Schema::table('mous', function (Blueprint $table) {
            if (!Schema::hasColumn('mous', 'lembar_survei')) {
                // Pakai TEXT supaya aman untuk path yang panjang
                // Letakkan setelah 'gambar_kerja' kalau kolom itu ada
                try {
                    $table->text('lembar_survei')->nullable()->after('gambar_kerja');
                } catch (\Throwable $e) {
                    // fallback kalau kolom 'gambar_kerja' tidak ada
                    $table->text('lembar_survei')->nullable();
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            if (Schema::hasColumn('mous', 'lembar_survei')) {
                $table->dropColumn('lembar_survei');
            }
        });
    }
};
