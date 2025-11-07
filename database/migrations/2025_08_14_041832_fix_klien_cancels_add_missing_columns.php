<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('klien_cancels', function (Blueprint $table) {
            if (!Schema::hasColumn('klien_cancels', 'klien_id')) {
                $table->unsignedBigInteger('klien_id')->nullable()->index();
            }
            if (!Schema::hasColumn('klien_cancels', 'alasan_cancel')) {
                $table->text('alasan_cancel')->nullable();
            }
            if (!Schema::hasColumn('klien_cancels', 'canceled_by')) {
                $table->unsignedBigInteger('canceled_by')->nullable()->index();
            }
            if (!Schema::hasColumn('klien_cancels', 'canceled_at')) {
                $table->timestamp('canceled_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('klien_cancels', function (Blueprint $table) {
            if (Schema::hasColumn('klien_cancels', 'canceled_at')) $table->dropColumn('canceled_at');
            if (Schema::hasColumn('klien_cancels', 'canceled_by'))  $table->dropColumn('canceled_by');
            if (Schema::hasColumn('klien_cancels', 'alasan_cancel'))$table->dropColumn('alasan_cancel');
            if (Schema::hasColumn('klien_cancels', 'klien_id'))     $table->dropColumn('klien_id');
        });
    }
};
