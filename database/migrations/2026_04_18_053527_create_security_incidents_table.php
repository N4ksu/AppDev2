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
        Schema::create('security_incidents', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // 'brute_force', 'credential_stuffing', 'multi_account_target', etc.
            $table->string('severity')->default('low'); // 'low', 'medium', 'high', 'critical'
            $table->string('status')->default('open'); // 'open', 'investigating', 'resolved', 'dismissed'
            $table->string('source_ip')->nullable()->index();
            $table->string('target_identifier')->nullable()->index(); // email or user id pattern
            $table->foreignId('affected_user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->integer('logs_count')->default(0);
            $table->timestamp('first_detected_at')->nullable();
            $table->timestamp('last_detected_at')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_incidents');
    }
};
