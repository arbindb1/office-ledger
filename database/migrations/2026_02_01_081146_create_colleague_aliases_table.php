<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('colleague_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('colleague_id')
                ->constrained('colleagues')
                ->cascadeOnDelete();

            $table->string('alias');
            $table->string('normalized_alias'); // lowercase/trimmed version
            $table->timestamps();

            $table->unique(['colleague_id', 'normalized_alias'], 'uniq_colleague_alias');
            $table->index('normalized_alias');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('colleague_aliases');
    }
};
