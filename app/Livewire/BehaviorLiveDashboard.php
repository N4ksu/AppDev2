<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Title;
use App\Models\BehaviorSample;

#[Title('Live Behaviour Monitor')]
class BehaviorLiveDashboard extends Component
{
    public $userId;

    public function mount()
    {
        $this->userId = auth()->id();
    }

    public function render()
    {
        $latestSample = BehaviorSample::where('user_id', $this->userId)->latest()->first();
        
        $samples = BehaviorSample::where('user_id', $this->userId)
                     ->orderBy('created_at', 'desc')
                     ->take(20)
                     ->get()
                     ->reverse()
                     ->values();
                     
        $avgTyping = $samples->count() > 0 ? $samples->take(-10)->avg('typing_speed') : 0;
        $avgMouse = $samples->count() > 0 ? $samples->take(-10)->avg('mouse_velocity') : 0;

        return view('livewire.behavior-live-dashboard', compact('latestSample', 'avgTyping', 'avgMouse', 'samples'));
    }
}
