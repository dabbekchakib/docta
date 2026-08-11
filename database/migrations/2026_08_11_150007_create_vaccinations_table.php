<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vaccinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('vaccine_name');
            $table->unsignedSmallInteger('dose_number')->nullable();
            $table->date('administered_at')->nullable();
            $table->date('next_due_at')->nullable();
            $table->string('provider')->nullable();
            $table->string('batch_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('administered_at');
            $table->index('next_due_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vaccinations');
    }
};
