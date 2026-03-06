<?php

namespace App\Livewire\SuperAdmin;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Process\Process;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.super-admin')]
class PlatformSettings extends Component
{
    public float $commissionPercent = 5;
    public string $defaultGateway = 'stripe';
    public bool $maintenanceMode = false;
    public string $realtimeMode = 'polling';
    public string $soundTriggerMode = 'polling';

    protected array $allowedCommands = [
        'cache:clear' => ['type' => 'artisan', 'command' => 'cache:clear'],
        'config:clear' => ['type' => 'artisan', 'command' => 'config:clear'],
        'route:clear' => ['type' => 'artisan', 'command' => 'route:clear'],
        'view:clear' => ['type' => 'artisan', 'command' => 'view:clear'],
        'optimize' => ['type' => 'artisan', 'command' => 'optimize'],
        'optimize:clear' => ['type' => 'artisan', 'command' => 'optimize:clear'],
        'migrate' => ['type' => 'artisan', 'command' => 'migrate', 'params' => ['--force' => true]],
        'storage:link' => ['type' => 'artisan', 'command' => 'storage:link'],
        'composer install' => ['type' => 'shell', 'command' => ['composer', 'install', '--no-interaction', '--prefer-dist']],
        'composer update' => ['type' => 'shell', 'command' => ['composer', 'update', '--no-interaction', '--prefer-dist']],
        'composer dump-autoload' => ['type' => 'shell', 'command' => ['composer', 'dump-autoload', '-o']],
    ];

    public function runCommand(string $command): void
    {
        if (! isset($this->allowedCommands[$command])) {
            session()->flash('commandOutput', 'Command is not allowed.');
            $this->dispatch('toast', message: 'Command not allowed.');

            return;
        }

        $config = $this->allowedCommands[$command];

        try {
            $output = '';

            if ($config['type'] === 'artisan') {
                Artisan::call($config['command'], $config['params'] ?? []);
                $output = Artisan::output();
            }

            if ($config['type'] === 'shell') {
                $process = new Process($config['command'], base_path());
                $process->setTimeout(1800);
                $process->run();
                $output = trim($process->getOutput()."\n".$process->getErrorOutput());

                if (! $process->isSuccessful()) {
                    throw new \RuntimeException($output !== '' ? $output : 'Shell command failed.');
                }
            }

            session()->flash('commandOutput', $output ?: 'Command executed.');
            $this->dispatch('toast', message: ucfirst($command) . ' executed.');
        } catch (\Exception $e) {
            session()->flash('commandOutput', 'Error: ' . $e->getMessage());
            $this->dispatch('toast', message: 'Error running command.');
        }
    }

    public function mount(): void
    {
        $this->commissionPercent = (float) Cache::get('platform_commission_percent', 5);
        $this->defaultGateway = (string) Cache::get('platform_default_gateway', 'stripe');
        $this->maintenanceMode = (bool) Cache::get('platform_maintenance_mode', false);
        $this->realtimeMode = (string) Cache::get('platform_realtime_mode', 'polling');
        $this->soundTriggerMode = (string) Cache::get('platform_sound_trigger_mode', 'polling');
    }

    public function save(): void
    {
        $this->validate([
            'commissionPercent' => ['required', 'numeric', 'min:0', 'max:100'],
            'defaultGateway' => ['required', 'in:stripe,razorpay'],
            'maintenanceMode' => ['boolean'],
            'realtimeMode' => ['required', 'in:polling,websocket'],
            'soundTriggerMode' => ['required', 'in:polling,websocket'],
        ]);

        Cache::forever('platform_commission_percent', $this->commissionPercent);
        Cache::forever('platform_default_gateway', $this->defaultGateway);
        Cache::forever('platform_maintenance_mode', $this->maintenanceMode);
        Cache::forever('platform_realtime_mode', $this->realtimeMode);
        Cache::forever('platform_sound_trigger_mode', $this->soundTriggerMode);
        $this->dispatch('toast', message: 'Platform settings saved.');
    }

    public function render()
    {
        return view('livewire.super-admin.platform-settings');
    }
}
