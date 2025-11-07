<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            // tambahkan kolom kalau belum ada
            if (! Schema::hasColumn('mous', 'tahap_akhir_id')) {
                $table->unsignedBigInteger('tahap_akhir_id')
                      ->nullable()
                      ->after('klien_id');

                // opsional: index / foreign key
                // $table->foreign('tahap_akhir_id')
                //       ->references('id')->on('tahap_akhirs')
                //       ->nullOnDelete(); // atau ->cascadeOnDelete();
            }

            // kalau sebelumnya masalah lembar_survei, sekalian pastikan ada
            if (! Schema::hasColumn('mous', 'lembar_survei')) {
                $table->string('lembar_survei')->nullable()->after('gambar_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            // jika pakai FK, drop FK dulu
            // if (Schema::hasColumn('mous', 'tahap_akhir_id')) {
            //     $table->dropForeign(['tahap_akhir_id']);
            // }

            if (Schema::hasColumn('mous', 'tahap_akhir_id')) {
                $table->dropColumn('tahap_akhir_id');
            }
            // jangan lupa, hanya kalau kamu tadi menambahkan
            // if (Schema::hasColumn('mous', 'lembar_survei')) {
            //     $table->dropColumn('lembar_survei');
            // }
        });
    }
};
