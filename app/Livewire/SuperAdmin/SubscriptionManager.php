<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.super-admin')]
class SubscriptionManager extends Component
{
    use WithPagination;

    public ?int $editingId = null;
    public string $search = '';
    public int $adminId = 0;
    public int $maxTournaments = 1;
    public int $maxTeams = 10;
    public int $maxPlayers = 200;
    public string $expiresAt = '';
    public bool $isActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function edit(int $subscriptionId): void
    {
        $subscription = Subscription::query()->findOrFail($subscriptionId);

        $this->editingId = $subscription->id;
        $this->adminId = (int) $subscription->admin_id;
        $this->maxTournaments = (int) $subscription->max_tournaments;
        $this->maxTeams = (int) $subscription->max_teams;
        $this->maxPlayers = (int) $subscription->max_players;
        $this->expiresAt = $subscription->expires_at
            ? Carbon::parse($subscription->expires_at)->format('Y-m-d')
            : '';
        $this->isActive = (bool) $subscription->is_active;
    }

    public function save(): void
    {
        $this->validate([
            'adminId' => ['required', 'integer', 'exists:users,id'],
            'maxTournaments' => ['required', 'integer', 'min:1'],
            'maxTeams' => ['required', 'integer', 'min:1'],
            'maxPlayers' => ['required', 'integer', 'min:1'],
            'expiresAt' => ['required', 'date'],
            'isActive' => ['boolean'],
        ]);

        $payload = [
            'admin_id' => $this->adminId,
            'max_tournaments' => $this->maxTournaments,
            'max_teams' => $this->maxTeams,
            'max_players' => $this->maxPlayers,
            'expires_at' => $this->expiresAt,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId) {
            Subscription::query()->whereKey($this->editingId)->update($payload);
            $message = 'Subscription updated.';
        } else {
            Subscription::query()->create($payload);
            $message = 'Subscription created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function toggle(int $subscriptionId): void
    {
        $subscription = Subscription::query()->findOrFail($subscriptionId);
        $subscription->update(['is_active' => ! $subscription->is_active]);
        $this->dispatch('toast', message: 'Subscription status updated.');
    }

    public function delete(int $subscriptionId): void
    {
        Subscription::query()->whereKey($subscriptionId)->delete();
        $this->dispatch('toast', message: 'Subscription deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['editingId', 'adminId', 'maxTournaments', 'maxTeams', 'maxPlayers', 'expiresAt', 'isActive']);
        $this->maxTournaments = 1;
        $this->maxTeams = 10;
        $this->maxPlayers = 200;
        $this->isActive = true;
    }

    public function render()
    {
        return view('livewire.super-admin.subscription-manager', [
            'admins' => User::role('Admin')->get(['id', 'name']),
            'subscriptions' => Subscription::query()
                ->with('admin:id,name,email')
                ->when($this->search !== '', function ($query) {
                    $query->whereHas('admin', function ($inner) {
                        $inner->where('name', 'like', '%'.$this->search.'%')
                            ->orWhere('email', 'like', '%'.$this->search.'%');
                    });
                })
                ->latest()
                ->paginate(10),
        ]);
    }
}
