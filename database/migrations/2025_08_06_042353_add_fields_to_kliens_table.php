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
        Schema::table('kliens', function (Blueprint $table) {
            $table->string('email')->nullable();
            $table->string('alamat_tinggal')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('kode_proyek')->nullable();
            $table->string('kelas')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            $table->dropColumn(['email', 'alamat_tinggal', 'no_hp', 'kode_proyek', 'kelas']);
        });
    }
};
