<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
    Schema::create('data_plans', function (Blueprint $table) {
        $table->id();
        $table->string('network'); // MTN, GLO, etc.
        $table->string('name');    // e.g. "1GB SME"
        $table->string('plan_id'); // The API ID from your provider
        $table->decimal('cost_price', 10, 2); // What YOU pay
        $table->decimal('price', 10, 2);      // What USERS pay (Selling Price)
        $table->boolean('is_active')->default(true);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_plans');
    }
};
