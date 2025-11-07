<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skema_cancels', function (Blueprint $table) {
            if (!Schema::hasColumn('skema_cancels', 'lokasi_lahan')) {
                $table->string('lokasi_lahan')->nullable()->after('kode_proyek');
            }
        });
    }

    public function down(): void
    {
        Schema::table('skema_cancels', function (Blueprint $table) {
            if (Schema::hasColumn('skema_cancels', 'lokasi_lahan')) {
                $table->dropColumn('lokasi_lahan');
            }
        });
    }
};
