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
    Schema::create('referrals', function (Blueprint $table) {
        $table->id();

        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('referred_user_id')->nullable();   // user who signed up via referral

        $table->decimal('bonus_amount', 12, 2)->default(0);   // referral bonus
        $table->enum('status', ['pending', 'rewarded'])->default('pending');

        $table->timestamps();
    });
}
};
