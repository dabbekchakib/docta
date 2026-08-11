<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lifestyles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('smoking_status')->default('unknown');
            $table->string('smoking_quantity')->nullable();
            $table->string('alcohol_status')->default('unknown');
            $table->string('physical_activity')->nullable();
            $table->string('diet')->nullable();
            $table->string('sleep_quality')->nullable();
            $table->string('occupation_risk')->nullable();
            $table->text('other_risks')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('medical_record_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifestyles');
    }
};
