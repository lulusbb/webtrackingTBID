<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom tanggal_mulai ke tabel proyekjalans.
     */
    public function up(): void
    {
        Schema::table('proyekjalans', function (Blueprint $table) {
            $table->timestamp('tanggal_mulai')->nullable()->after('tanggal_masuk');
        });
    }

    /**
     * Hapus kolom tanggal_mulai jika di-rollback.
     */
    public function down(): void
    {
        Schema::table('proyekjalans', function (Blueprint $table) {
            $table->dropColumn('tanggal_mulai');
        });
    }
};
