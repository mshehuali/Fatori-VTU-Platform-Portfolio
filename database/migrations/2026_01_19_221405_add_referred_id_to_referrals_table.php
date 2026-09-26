<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            // Check if column exists to prevent duplicate errors
            if (!Schema::hasColumn('referrals', 'referred_id')) {
                $table->foreignId('referred_id')
                      ->nullable()
                      ->after('user_id')
                      ->constrained('users')
                      ->onDelete('cascade');
            }
            
            // Ensure bonus_amount exists
            if (!Schema::hasColumn('referrals', 'bonus_amount')) {
                $table->decimal('bonus_amount', 10, 2)->default(200.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('referrals', function (Blueprint $table) {
            $table->dropForeign(['referred_id']);
            $table->dropColumn('referred_id');
        });
    }
};