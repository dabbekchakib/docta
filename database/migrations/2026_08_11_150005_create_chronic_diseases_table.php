<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chronic_diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('disease_name');
            $table->string('icd_code')->nullable();
            $table->date('diagnosed_at')->nullable();
            $table->string('status')->default('active');
            $table->string('severity')->default('moderate');
            $table->text('treatment')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('icd_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chronic_diseases');
    }
};
