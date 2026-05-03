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
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->integer('failed_attempts')->default(0)->after('user_agent');
            $table->integer('risk_score')->default(0)->after('failed_attempts');
            $table->string('risk_level')->default('normal')->after('risk_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
