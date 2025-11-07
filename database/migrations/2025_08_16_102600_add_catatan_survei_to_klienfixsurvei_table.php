<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            // letakkan setelah scheduled_by agar grup penjadwalan berdekatan
            if (!Schema::hasColumn('klienfixsurvei', 'catatan_survei')) {
                $table->text('catatan_survei')->nullable()->after('scheduled_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('klienfixsurvei', function (Blueprint $table) {
            if (Schema::hasColumn('klienfixsurvei', 'catatan_survei')) {
                $table->dropColumn('catatan_survei');
            }
        });
    }
};
