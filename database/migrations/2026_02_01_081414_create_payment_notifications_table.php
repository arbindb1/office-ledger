<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();

            // Where it came from
            $table->string('device_id');
            $table->string('android_package')->nullable();
            $table->string('notification_uid')->nullable();
            $table->string('title')->nullable();
            $table->text('raw_text');
            $table->dateTime('posted_at');

            // Idempotency (server-generated)
            $table->string('hash')->unique();

            // Parsed fields
            $table->decimal('parsed_amount', 10, 2)->nullable();
            $table->string('parsed_sender')->nullable();
            $table->string('parsed_txn_id')->nullable();
            $table->unsignedSmallInteger('parse_confidence')->default(0);

            // Match fields
            $table->foreignId('matched_colleague_id')
                ->nullable()
                ->constrained('colleagues')
                ->nullOnDelete();

            $table->unsignedSmallInteger('match_confidence')->default(0);
            $table->string('match_strategy')->nullable(); // exact|alias|fuzzy|manual

            // Status
            $table->string('status')->default('unmatched'); // unmatched|matched|applied|ignored

            $table->timestamps();

            $table->index('status');
            $table->index('posted_at');
            $table->index('matched_colleague_id');
            $table->index('device_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_notifications');
    }
};
