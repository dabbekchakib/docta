<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('test_category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->string('sample_type')->default('blood');
            $table->string('unit')->nullable();
            $table->string('default_reference_value')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_fasting')->default(false);
            $table->text('instructions')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('test_category_id');
            $table->index('sample_type');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_tests');
    }
};
