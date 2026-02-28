<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('vendor_name')->nullable();
            $table->dateTime('ordered_at');
            $table->string('status')->default('draft'); 
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->index('ordered_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('order_batches', function (Blueprint $table) {
            $table->dropColumn('title');
                        $table->dropColumn('created_by_user_id');

                        $table->dropColumn('vendor_name');

                        $table->dropColumn('ordered_at');

                        $table->dropColumn('status');

                        $table->dropColumn('notes');

        });
    }
};
