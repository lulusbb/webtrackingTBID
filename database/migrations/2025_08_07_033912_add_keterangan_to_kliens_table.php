<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('kliens', function (Blueprint $table) {
            $table->text('keterangan')->nullable()->after('tanggal_masuk');
        });
    }

    public function down()
    {
        Schema::table('kliens', function (Blueprint $table) {
            $table->dropColumn('keterangan');
        });
    }
};
