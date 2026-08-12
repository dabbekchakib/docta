<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('laboratory_test_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->string('sample_type')->default('blood');
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index('laboratory_request_id');
            $table->index('laboratory_test_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_request_items');
    }
};
