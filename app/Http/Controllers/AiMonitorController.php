<?php

namespace App\Http\Controllers;

use App\Models\SecurityInsight;
use App\Models\User;

class AiMonitorController extends Controller
{
    public function index()
    {
        $aiLogs = SecurityInsight::with('loginLog')
                    ->latest()
                    ->take(50)
                    ->get();

        $highRiskCount = SecurityInsight::whereIn('severity', ['high', 'critical'])
                          ->whereDate('created_at', today())
                          ->count();

        $blockedCount = SecurityInsight::where('final_action', 'deny')->count();
        $degradedCount = SecurityInsight::where('provider_status', '!=', 'success')
            ->whereDate('created_at', today())
            ->count();
        $calibratedUsers = User::whereNotNull('calibrated_at')->count();
        $totalUsers = User::count();

        return view('admin.ai-monitor', compact(
            'aiLogs',
            'highRiskCount',
            'blockedCount',
            'degradedCount',
            'calibratedUsers',
            'totalUsers'
        ));
    }
}
