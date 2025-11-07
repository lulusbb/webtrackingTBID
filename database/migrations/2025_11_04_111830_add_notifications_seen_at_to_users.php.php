<?php

// database/migrations/2025_11_04_000000_add_notifications_seen_at_to_users.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'notifications_seen_at')) {
                $table->timestamp('notifications_seen_at')->nullable()->after('remember_token');
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'notifications_seen_at')) {
                $table->dropColumn('notifications_seen_at');
            }
        });
    }
};

