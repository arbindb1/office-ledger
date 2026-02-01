<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_batches', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('vendor_name')->nullable();
            $table->dateTime('ordered_at');
            $table->string('status')->default('draft'); // draft|finalized|cancelled
            $table->text('notes')->nullable();

            // Optional if you later add auth/users:
            $table->unsignedBigInteger('created_by_user_id')->nullable();

            $table->timestamps();

            $table->index('ordered_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_batches');
    }
};
