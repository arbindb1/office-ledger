<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_batch_id')
                ->constrained('order_batches')
                ->cascadeOnDelete();

            $table->foreignId('colleague_id')
                ->constrained('colleagues')
                ->restrictOnDelete();

            $table->string('item_name');
            $table->unsignedInteger('quantity')->default(1);

            $table->decimal('unit_price', 10, 2);
            $table->decimal('line_total', 10, 2);

            $table->timestamps();

            $table->index(['order_batch_id', 'colleague_id'], 'idx_batch_colleague');
            $table->index('colleague_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
