<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->string('patient_number')->unique();
            $table->string('title')->default('mr');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('birth_date')->nullable();
            $table->string('cin')->nullable()->unique();
            $table->string('photo')->nullable();
            $table->string('phone');
            $table->string('phone_secondary')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height', 5, 2)->nullable();
            $table->decimal('weight', 5, 2)->nullable();
            $table->text('allergies')->nullable();
            $table->text('medical_history')->nullable();
            $table->text('chronic_diseases')->nullable();
            $table->text('disability')->nullable();
            $table->text('permanent_treatments')->nullable();
            $table->text('medical_notes')->nullable();
            $table->boolean('has_cnam')->default(false);
            $table->string('cnam_number')->nullable();
            $table->boolean('has_insurance')->default(false);
            $table->string('insurance_number')->nullable();
            $table->date('insurance_expires_at')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('emergency_relation')->nullable();
            $table->string('emergency_phone')->nullable();
            $table->string('emergency_address')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('gender');
            $table->index('governorate');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
