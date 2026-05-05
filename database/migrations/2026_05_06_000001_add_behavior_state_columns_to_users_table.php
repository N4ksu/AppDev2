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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('calibrated_at')->nullable()->after('last_login_ip');
            $table->string('calibration_status', 40)->default('not_calibrated')->after('calibrated_at');
            $table->unsignedInteger('calibration_version')->default(1)->after('calibration_status');
            $table->unsignedInteger('behavior_sample_count')->default(0)->after('calibration_version');
            $table->timestamp('last_behavior_verification_at')->nullable()->after('behavior_sample_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'calibrated_at',
                'calibration_status',
                'calibration_version',
                'behavior_sample_count',
                'last_behavior_verification_at',
            ]);
        });
    }
};
