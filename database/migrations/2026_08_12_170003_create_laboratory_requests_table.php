<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('laboratory_id')->nullable()->constrained()->nullOnDelete();
            $table->date('requested_at');
            $table->string('priority')->default('normal');
            $table->string('status')->default('draft');
            $table->text('clinical_information')->nullable();
            $table->text('doctor_notes')->nullable();
            $table->text('patient_instructions')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('patient_id');
            $table->index('doctor_id');
            $table->index('laboratory_id');
            $table->index('consultation_id');
            $table->index('status');
            $table->index('priority');
            $table->index('requested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_requests');
    }
};
