<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // 1. Add friend_name if missing
            if (!Schema::hasColumn('referrals', 'friend_name')) {
                $table->string('friend_name')->nullable()->after('user_id');
            }

            // 2. Add referred_id if missing
            if (!Schema::hasColumn('referrals', 'referred_id')) {
                $table->foreignId('referred_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            if (Schema::hasColumn('referrals', 'friend_name')) {
                $table->dropColumn('friend_name');
            }
            if (Schema::hasColumn('referrals', 'referred_id')) {
                $table->dropForeign(['referred_id']);
                $table->dropColumn('referred_id');
            }
        });
    }
};