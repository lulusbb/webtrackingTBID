<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
    {
        if (Schema::hasTable('kliens')) {
            Schema::table('kliens', function (Blueprint $table) {
                if (!Schema::hasColumn('kliens', 'tanggal_masuk')) {
                    $table->date('tanggal_masuk')->nullable()->after('luas_lahan');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('kliens')) {
            Schema::table('kliens', function (Blueprint $table) {
                if (Schema::hasColumn('kliens', 'tanggal_masuk')) {
                    $table->dropColumn('tanggal_masuk');
                }
            });
        }
    }
};