<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.super-admin')]
class PlatformSettings extends Component
{
    public float $commissionPercent = 5;
    public string $defaultGateway = 'stripe';
    public bool $maintenanceMode = false;

    public function mount(): void
    {
        $this->commissionPercent = (float) Cache::get('platform_commission_percent', 5);
        $this->defaultGateway = (string) Cache::get('platform_default_gateway', 'stripe');
        $this->maintenanceMode = (bool) Cache::get('platform_maintenance_mode', false);
    }

    public function save(): void
    {
        $this->validate([
            'commissionPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'defaultGateway' => ['required', 'in:stripe,razorpay'],
            'maintenanceMode' => ['boolean'],
        ]);

        Cache::forever('platform_commission_percent', $this->commissionPercent);
        Cache::forever('platform_default_gateway', $this->defaultGateway);
        Cache::forever('platform_maintenance_mode', $this->maintenanceMode);
        $this->dispatch('toast', message: 'Platform settings saved.');
    }

    public function render()
    {
        return view('livewire.super-admin.platform-settings');
    }
}
