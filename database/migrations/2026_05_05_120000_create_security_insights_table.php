<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('security_insights', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('login_log_id')->constrained()->cascadeOnDelete();
            $table->string('severity', 20);
            $table->text('reason');
            $table->string('recommendation', 40)->default('monitor');
            $table->string('model_name', 120)->default('gemini');
            $table->string('provider_status', 20)->default('degraded');
            $table->string('final_action', 20)->default('monitor');
            $table->string('local_risk_band', 20)->default('safe');
            $table->json('ai_response_json')->nullable();
            $table->json('decision_metadata')->nullable();
            $table->timestamps();

            $table->index(['login_log_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['severity', 'provider_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_insights');
    }
};
