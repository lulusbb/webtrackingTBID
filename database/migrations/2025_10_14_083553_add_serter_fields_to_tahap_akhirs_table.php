<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tahap_akhirs', function (Blueprint $table) {
            if (!Schema::hasColumn('tahap_akhirs', 'serter_at')) {
                $table->timestamp('serter_at')->nullable()->after('updated_at');
            }
            if (!Schema::hasColumn('tahap_akhirs', 'status_akhir') && !Schema::hasColumn('tahap_akhirs', 'status')) {
                // opsional; kalau sudah ada "status", tak perlu bikin "status_akhir"
                $table->string('status_akhir', 50)->nullable()->after('serter_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tahap_akhirs', function (Blueprint $table) {
            if (Schema::hasColumn('tahap_akhirs', 'status_akhir')) {
                $table->dropColumn('status_akhir');
            }
            if (Schema::hasColumn('tahap_akhirs', 'serter_at')) {
                $table->dropColumn('serter_at');
            }
        });
    }
};

