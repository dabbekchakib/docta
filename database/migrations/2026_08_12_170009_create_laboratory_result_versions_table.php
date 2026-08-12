<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_result_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_result_id')->constrained()->cascadeOnDelete();
            $table->string('previous_value')->nullable();
            $table->decimal('previous_numeric_value', 12, 3)->nullable();
            $table->string('new_value')->nullable();
            $table->decimal('new_numeric_value', 12, 3)->nullable();
            $table->text('reason')->nullable();
            $table->foreignId('corrected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('corrected_at');
            $table->timestamps();

            $table->index('laboratory_result_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_result_versions');
    }
};
