<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_test_id')->constrained()->cascadeOnDelete();
            $table->string('gender')->default('all');
            $table->integer('age_min')->nullable();
            $table->integer('age_max')->nullable();
            $table->decimal('min_value', 12, 3)->nullable();
            $table->decimal('max_value', 12, 3)->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_text')->nullable();
            $table->timestamps();

            $table->index('laboratory_test_id');
            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_ranges');
    }
};
