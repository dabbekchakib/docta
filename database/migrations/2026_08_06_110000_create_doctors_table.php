<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('doctor_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('speciality');
            $table->string('sub_speciality')->nullable();
            $table->string('order_number')->nullable()->unique();
            $table->string('national_id')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->string('postal_code')->nullable();
            $table->text('biography')->nullable();
            $table->decimal('consultation_fee', 10, 3)->nullable();
            $table->unsignedInteger('consultation_duration')->nullable();
            $table->date('start_working_date')->nullable();
            $table->string('signature_image')->nullable();
            $table->string('diploma_file')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('speciality');
            $table->index('status');
            $table->index('governorate');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctors');
    }
};
