<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->string('prescription_number')->unique();
            $table->foreignId('patient_id')->constrained()->cascadeOnDelete();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('consultation_id')->nullable()->constrained()->nullOnDelete();
            $table->date('prescription_date');
            $table->string('status')->default('draft');
            $table->text('notes')->nullable();
            $table->date('valid_until')->nullable();
            $table->string('verification_token', 64)->nullable()->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index('prescription_date');
            $table->index('status');
            $table->index(['patient_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescriptions');
    }
};
