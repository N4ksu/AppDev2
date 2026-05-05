<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function toggleChat(Request $request)
    {
        $settings = app(SettingsService::class);
        $current = $settings->get('ai_chat_enabled', '1');
        $new = $current === '1' ? '0' : '1';
        
        $settings->set('ai_chat_enabled', $new);
        
        return back()->with('status', 'AI Chat assistance has been ' . ($new === '1' ? 'enabled' : 'disabled') . ' successfully.');
    }
}
