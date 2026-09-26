<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWalletAuditsTable extends Migration
{
    public function up(): void
    {
        Schema::create('wallet_audits', function (Blueprint $table) {
            $table->id();

            // Ledger must always belong to a wallet and user
            $table->foreignId('wallet_id')
                ->constrained('wallets')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Transaction direction
            $table->enum('direction', ['credit', 'debit']);

            // Source of transaction (funding, airtime, data, etc.)
            $table->string('source');

            $table->string('description')->nullable();

            // Unique reference for idempotency
            $table->string('reference')->unique();

            // Financial values (never nullable)
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);

            // success | failed | reversed
            $table->string('status')->default('success');

            // Extra gateway / VTU metadata
            $table->json('meta')->nullable();

            $table->timestamps();

            // Helpful indexes
            $table->index(['wallet_id', 'direction']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_audits');
    }
}
