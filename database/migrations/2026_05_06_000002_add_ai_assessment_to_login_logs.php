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
        Schema::table('login_logs', function (Blueprint $table) {
            $table->integer('ai_risk_score')->nullable()->after('risk_level');
            $table->json('anomaly_flags')->nullable()->after('ai_risk_score');
            $table->text('explanation')->nullable()->after('anomaly_flags');
            $table->string('recommended_action')->nullable()->after('explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_logs', function (Blueprint $table) {
            $table->dropColumn(['ai_risk_score', 'anomaly_flags', 'explanation', 'recommended_action']);
        });
    }
};
