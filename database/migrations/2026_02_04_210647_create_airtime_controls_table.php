<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('airtime_controls', function (Blueprint $table) {
            $table->id();
            $table->string('network')->unique(); // MTN, GLO, AIRTEL, 9MOBILE
            $table->decimal('discount_percent', 5, 2)->default(0); // e.g. 2.00 means 2% OFF (User pays 98%)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('airtime_controls');
    }
};
