<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            // Simpan path file lembar diskusi (di disk 'public')
            $table->string('lembar_diskusi')->nullable()->after('foto_eksisting');
        });
    }

    public function down(): void
    {
        Schema::table('kliens', function (Blueprint $table) {
            $table->dropColumn('lembar_diskusi');
        });
    }
};
