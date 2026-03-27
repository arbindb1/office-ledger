<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin');
            $table->unsignedBigInteger('colleague_id')->nullable();
            
            $table->foreign('colleague_id')->references('id')->on('colleagues')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['colleague_id']);
            $table->dropColumn(['role', 'colleague_id']);
        });
    }
};
