<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->string('consultation_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('consultation_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('type')->default('first_visit');
            $table->text('reason')->nullable();
            $table->text('symptoms')->nullable();
            $table->longText('clinical_examination')->nullable();
            $table->longText('diagnosis')->nullable();
            $table->text('secondary_diagnoses')->nullable();
            $table->longText('medical_notes')->nullable();
            $table->longText('treatment_plan')->nullable();
            $table->longText('recommendations')->nullable();
            $table->date('follow_up_date')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('consultation_date');
            $table->index('status');
            $table->index('doctor_id');
            $table->index('patient_id');
            $table->index('appointment_id');
            $table->unique(['doctor_id', 'appointment_id'], 'consultations_doctor_appointment_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
