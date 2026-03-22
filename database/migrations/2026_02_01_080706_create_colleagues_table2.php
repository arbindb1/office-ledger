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
        Schema::create('colleagues', function (Blueprint $table) {
            // id: bigint unsigned, auto_increment, primary key
            $table->id(); 

            // display_name: varchar(255), not null, indexed
            $table->string('display_name')->index();

            // phone: varchar(255), nullable, indexed
            $table->string('phone')->nullable()->index();

            // is_active: tinyint(1), default 1
            $table->boolean('is_active')->default(true);

            // notes: text, nullable
            $table->text('notes')->nullable();

            // created_at & updated_at: timestamp, nullable
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('colleagues');
    }
};