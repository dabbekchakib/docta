<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laboratory_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('laboratory_request_item_id')->nullable()->constrained()->nullOnDelete();
            $table->string('sample_number')->unique();
            $table->string('sample_type')->default('blood');
            $table->dateTime('collected_at')->nullable();
            $table->foreignId('collected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('received_at')->nullable();
            $table->string('status')->default('pending');
            $table->string('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('laboratory_request_id');
            $table->index('laboratory_request_item_id');
            $table->index('status');
            $table->index('collected_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('samples');
    }
};
