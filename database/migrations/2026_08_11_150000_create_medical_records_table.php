<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->string('medical_record_number')->unique();
            $table->string('blood_group')->nullable();
            $table->string('rh_factor')->nullable();
            $table->text('general_notes')->nullable();
            $table->text('emergency_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('blood_group');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_records');
    }
};
