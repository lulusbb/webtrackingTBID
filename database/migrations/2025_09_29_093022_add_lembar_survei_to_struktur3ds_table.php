<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('struktur_3ds', function (Blueprint $table) {
            if (!Schema::hasColumn('struktur_3ds', 'lembar_survei')) {
                // taruh setelah gambar_kerja biar rapi
                $table->string('lembar_survei')->nullable()->after('gambar_kerja');
            }
        });
    }

    public function down(): void
    {
        Schema::table('struktur_3ds', function (Blueprint $table) {
            if (Schema::hasColumn('struktur_3ds', 'lembar_survei')) {
                $table->dropColumn('lembar_survei');
            }
        });
    }
};

