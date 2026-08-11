<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surgical_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('procedure_name');
            $table->string('hospital')->nullable();
            $table->string('surgeon')->nullable();
            $table->date('performed_at')->nullable();
            $table->text('reason')->nullable();
            $table->text('complications')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surgical_histories');
    }
};
