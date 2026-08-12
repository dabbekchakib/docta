<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_item_id')->constrained()->cascadeOnDelete();
            $table->string('parameter_name');
            $table->string('value')->nullable();
            $table->decimal('numeric_value', 12, 3)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('reference_min', 12, 3)->nullable();
            $table->decimal('reference_max', 12, 3)->nullable();
            $table->string('reference_text')->nullable();
            $table->string('abnormality')->default('normal');
            $table->text('comment')->nullable();
            $table->dateTime('resulted_at')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('laboratory_request_item_id');
            $table->index('abnormality');
            $table->index('validated_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_results');
    }
};
