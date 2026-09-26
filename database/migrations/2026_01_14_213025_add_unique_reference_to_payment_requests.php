<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexName = 'payment_requests_reference_unique';

        if (! Schema::hasIndex('payment_requests', $indexName)) {
            Schema::table('payment_requests', function (Blueprint $table) use ($indexName) {
                $table->unique('reference', $indexName);
            });
        }
    }

    public function down(): void
    {
        $indexName = 'payment_requests_reference_unique';

        if (Schema::hasIndex('payment_requests', $indexName)) {
            Schema::table('payment_requests', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        }
    }
};