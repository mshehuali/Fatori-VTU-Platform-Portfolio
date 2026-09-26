<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_failures', function (Blueprint $table) {
            $table->id();
            $table->string('reference');
            $table->string('gateway')->default('demo');
            $table->json('payload');
            $table->unsignedTinyInteger('retries')->default(0);
            $table->timestamps();

            $table->unique('reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_failures');
    }
};
