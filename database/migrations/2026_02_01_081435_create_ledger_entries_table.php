<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();

            $table->foreignId('colleague_id')
                ->constrained('colleagues')
                ->restrictOnDelete();

            $table->string('entry_type'); // debit|credit
            $table->decimal('amount', 10, 2);

            $table->string('source')->default('manual_adjustment'); 
            // order_batch|esewa_notification|manual_adjustment

            $table->foreignId('order_batch_id')
                ->nullable()
                ->constrained('order_batches')
                ->nullOnDelete();

            $table->foreignId('payment_notification_id')
                ->nullable()
                ->constrained('payment_notifications')
                ->nullOnDelete();

            // Extra safety (optional but recommended)
            $table->string('reference_key')->nullable();

            // debug/match metadata
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['colleague_id', 'entry_type'], 'idx_ledger_colleague_type');

            // Prevent one notification from creating multiple ledger credits
            $table->unique('payment_notification_id', 'uniq_ledger_payment_notification');

            // Optional: if you decide to always set reference_key (hash), you can enforce unique:
            // $table->unique('reference_key', 'uniq_ledger_reference_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
