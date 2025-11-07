<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // denahs: tambah 'sertifikat'
        Schema::table('denahs', function (Blueprint $table) {
            if (!Schema::hasColumn('denahs', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
        });

        // exteriors
        Schema::table('exteriors', function (Blueprint $table) {
            if (!Schema::hasColumn('exteriors', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('exteriors', 'foto_existing')) {
                $table->string('foto_existing')->nullable()->after('sertifikat');
            }
        });

        // meps
        Schema::table('meps', function (Blueprint $table) {
            if (!Schema::hasColumn('meps', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('meps', 'foto_existing')) {
                $table->string('foto_existing')->nullable()->after('sertifikat');
            }
        });

        // struktur_3ds
        Schema::table('struktur_3ds', function (Blueprint $table) {
            if (!Schema::hasColumn('struktur_3ds', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('struktur_3ds', 'foto_existing')) {
                $table->string('foto_existing')->nullable()->after('sertifikat');
            }
        });

        // skemas
        Schema::table('skemas', function (Blueprint $table) {
            if (!Schema::hasColumn('skemas', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('skemas', 'foto_existing')) {
                $table->string('foto_existing')->nullable()->after('sertifikat');
            }
        });

        // rabs
        Schema::table('rabs', function (Blueprint $table) {
            if (!Schema::hasColumn('rabs', 'sertifikat')) {
                $table->string('sertifikat')->nullable()->after('budget');
            }
            if (!Schema::hasColumn('rabs', 'foto_existing')) {
                $table->string('foto_existing')->nullable()->after('sertifikat');
            }
        });
    }

    public function down(): void
    {
        // denahs
        Schema::table('denahs', function (Blueprint $table) {
            if (Schema::hasColumn('denahs', 'sertifikat')) {
                $table->dropColumn('sertifikat');
            }
        });

        // exteriors
        Schema::table('exteriors', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('exteriors', 'sertifikat'))   $drops[] = 'sertifikat';
            if (Schema::hasColumn('exteriors', 'foto_existing')) $drops[] = 'foto_existing';
            if ($drops) $table->dropColumn($drops);
        });

        // meps
        Schema::table('meps', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('meps', 'sertifikat'))   $drops[] = 'sertifikat';
            if (Schema::hasColumn('meps', 'foto_existing')) $drops[] = 'foto_existing';
            if ($drops) $table->dropColumn($drops);
        });

        // struktur_3ds
        Schema::table('struktur_3ds', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('struktur_3ds', 'sertifikat'))   $drops[] = 'sertifikat';
            if (Schema::hasColumn('struktur_3ds', 'foto_existing')) $drops[] = 'foto_existing';
            if ($drops) $table->dropColumn($drops);
        });

        // skemas
        Schema::table('skemas', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('skemas', 'sertifikat'))   $drops[] = 'sertifikat';
            if (Schema::hasColumn('skemas', 'foto_existing')) $drops[] = 'foto_existing';
            if ($drops) $table->dropColumn($drops);
        });

        // rabs
        Schema::table('rabs', function (Blueprint $table) {
            $drops = [];
            if (Schema::hasColumn('rabs', 'sertifikat'))   $drops[] = 'sertifikat';
            if (Schema::hasColumn('rabs', 'foto_existing')) $drops[] = 'foto_existing';
            if ($drops) $table->dropColumn($drops);
        });
    }
};
