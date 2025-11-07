<?php

// database/migrations/2025_08_12_000001_add_status_to_kliens_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            if (!Schema::hasColumn('kliens', 'status')) {
                $table->string('status', 30)->nullable()->index()->after('kelas');
            }
        });
    }

    public function down(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            if (Schema::hasColumn('kliens', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
