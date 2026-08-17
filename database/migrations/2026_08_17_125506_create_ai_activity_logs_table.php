<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->nullable()->constrained('ai_conversations')->nullOnDelete();
            $table->string('tool_name');
            $table->text('request_summary');
            $table->text('action_performed')->nullable();
            $table->json('parameters')->nullable();
            $table->enum('status', ['success', 'error', 'denied', 'pending_confirmation']);
            $table->text('result_summary')->nullable();
            $table->timestamp('executed_at');
            $table->timestamps();

            $table->index(['user_id', 'tool_name']);
            $table->index('executed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_activity_logs');
    }
};
