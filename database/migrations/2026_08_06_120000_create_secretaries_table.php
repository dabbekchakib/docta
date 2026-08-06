<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secretaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('secretary_code')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('gender');
            $table->date('birth_date')->nullable();
            $table->string('photo')->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('cin')->nullable()->unique();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->string('postal_code')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('employee_number')->nullable()->unique();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('governorate');
            $table->index('hire_date');
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('secretaries');
    }
};
