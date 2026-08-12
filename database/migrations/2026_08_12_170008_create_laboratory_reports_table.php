<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laboratory_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->string('report_number')->unique();
            $table->date('report_date');
            $table->text('summary')->nullable();
            $table->text('comments')->nullable();
            $table->dateTime('validated_at')->nullable();
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('laboratory_request_id');
            $table->index('report_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laboratory_reports');
    }
};
