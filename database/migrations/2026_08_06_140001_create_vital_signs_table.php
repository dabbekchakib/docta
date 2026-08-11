<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vital_signs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('consultation_id')->constrained()->cascadeOnDelete();
            $table->decimal('temperature', 4, 1)->nullable();
            $table->decimal('weight', 5, 1)->nullable();
            $table->decimal('height', 5, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->string('blood_pressure')->nullable();
            $table->unsignedSmallInteger('heart_rate')->nullable();
            $table->unsignedTinyInteger('oxygen_saturation')->nullable();
            $table->unsignedTinyInteger('respiratory_rate')->nullable();
            $table->timestamps();

            $table->unique('consultation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vital_signs');
    }
};
