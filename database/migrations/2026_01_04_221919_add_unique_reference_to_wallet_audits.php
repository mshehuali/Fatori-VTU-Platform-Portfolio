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
        $logicalName = 'wallet_audits_reference_unique';

        if (! Schema::hasIndex('wallet_audits', $logicalName)) {
            Schema::table('wallet_audits', function (Blueprint $table) use ($logicalName) {
                $table->unique('reference', $logicalName);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $logicalName = 'wallet_audits_reference_unique';

        if (Schema::hasIndex('wallet_audits', $logicalName)) {
            Schema::table('wallet_audits', function (Blueprint $table) use ($logicalName) {
                $table->dropUnique($logicalName);
            });
        }
    }
};