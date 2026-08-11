<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('family_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('relative');
            $table->string('condition');
            $table->text('description')->nullable();
            $table->date('diagnosed_at')->nullable();
            $table->string('status')->default('unknown');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('relative');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_histories');
    }
};
