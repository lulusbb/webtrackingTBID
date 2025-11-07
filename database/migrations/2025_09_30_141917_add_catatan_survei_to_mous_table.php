<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            // tambahkan setelah kolom lembar_survei (opsional, hanya untuk urutan)
            $table->text('catatan_survei')->nullable()->after('lembar_survei');
        });
    }

    public function down(): void
    {
        Schema::table('mous', function (Blueprint $table) {
            $table->dropColumn('catatan_survei');
        });
    }
};
