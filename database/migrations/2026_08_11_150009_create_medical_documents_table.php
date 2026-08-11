<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('document_type')->default('autre');
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->string('issued_by')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('document_type');
            $table->index('document_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_documents');
    }
};
