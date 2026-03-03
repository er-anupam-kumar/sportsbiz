<?php

namespace App\Livewire\Admin\Teams;

use App\Models\Team;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Manager extends Component
{
    use WithFileUploads;
    use WithPagination;

    public int $tournamentId = 0;
    public ?int $editingId = null;
    public int $formTournamentId = 0;
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public float $walletBalance = 0;
    public bool $isLocked = false;
    public $logo;
    public ?string $existingLogoPath = null;
    public string $primaryColor = '';
    public string $secondaryColor = '';

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function save(): void
    {
        $editingTeam = null;
        $teamUserId = null;

        if ($this->editingId) {
            $editingTeam = Team::query()
                ->where('admin_id', auth()->id())
                ->with('user:id')
                ->findOrFail($this->editingId);
            $teamUserId = $editingTeam->user_id;
        }

        $passwordRules = ['nullable', 'string', 'min:8'];
        if (! $this->editingId || ! $teamUserId) {
            $passwordRules = ['required', 'string', 'min:8'];
        }

        $this->validate([
            'formTournamentId' => [
                'required',
                'integer',
                Rule::exists('tournaments', 'id')->where(fn ($query) => $query->where('admin_id', auth()->id())),
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('teams', 'name')
                    ->where(fn ($query) => $query->where('tournament_id', $this->formTournamentId))
                    ->ignore($this->editingId),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($teamUserId),
            ],
            'password' => $passwordRules,
            'walletBalance' => ['required', 'numeric', 'min:0'],
            'isLocked' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'primaryColor' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
            'secondaryColor' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6})$/'],
        ]);

        if ($teamUserId) {
            $teamUser = User::query()->findOrFail($teamUserId);
            $userPayload = [
                'name' => $this->name,
                'email' => $this->email,
                'parent_admin_id' => auth()->id(),
                'status' => 'active',
            ];

            if ($this->password !== '') {
                $userPayload['password'] = Hash::make($this->password);
            }

            $teamUser->update($userPayload);
        } else {
            $teamUser = User::query()->create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'parent_admin_id' => auth()->id(),
                'status' => 'active',
            ]);
            $teamUser->assignRole('Team');
            $teamUserId = $teamUser->id;
        }

        $payload = [
            'admin_id' => auth()->id(),
            'tournament_id' => $this->formTournamentId,
            'user_id' => $teamUserId,
            'name' => $this->name,
            'primary_color' => $this->primaryColor !== '' ? strtoupper($this->primaryColor) : null,
            'secondary_color' => $this->secondaryColor !== '' ? strtoupper($this->secondaryColor) : null,
            'wallet_balance' => $this->walletBalance,
            'is_locked' => $this->isLocked,
        ];

        if ($this->editingId) {
            $team = Team::query()
                ->where('admin_id', auth()->id())
                ->findOrFail($this->editingId);

            if ($this->logo) {
                if ($team->logo_path) {
                    Storage::disk('public')->delete($team->logo_path);
                }
                $payload['logo_path'] = $this->logo->store('teams/logos', 'public');
            }

            $team->update($payload);
            $message = 'Team updated.';
        } else {
            if ($this->logo) {
                $payload['logo_path'] = $this->logo->store('teams/logos', 'public');
            }
            Team::query()->create($payload);
            $message = 'Team created.';
        }

        $this->resetForm();
        $this->dispatch('toast', message: $message);
    }

    public function edit(int $teamId): void
    {
        $team = Team::query()->where('admin_id', auth()->id())->findOrFail($teamId);
        $this->editingId = $team->id;
        $this->formTournamentId = (int) $team->tournament_id;
        $this->name = $team->name;
        $this->email = (string) ($team->user?->email ?? '');
        $this->password = '';
        $this->walletBalance = (float) $team->wallet_balance;
        $this->isLocked = (bool) $team->is_locked;
        $this->existingLogoPath = $team->logo_path;
        $this->logo = null;
        $this->primaryColor = (string) ($team->primary_color ?? '');
        $this->secondaryColor = (string) ($team->secondary_color ?? '');
    }

    public function toggleLock(int $teamId): void
    {
        $team = Team::query()->where('admin_id', auth()->id())->findOrFail($teamId);
        $team->update(['is_locked' => ! $team->is_locked]);
        $this->dispatch('toast', message: 'Team lock updated.');
    }

    public function delete(int $teamId): void
    {
        $team = Team::query()->where('admin_id', auth()->id())->findOrFail($teamId);
        $teamUserId = $team->user_id;

        if ($team->logo_path) {
            Storage::disk('public')->delete($team->logo_path);
        }

        $team->delete();

        if ($teamUserId) {
            User::query()->whereKey($teamUserId)->delete();
        }

        if ($this->editingId === $teamId) {
            $this->resetForm();
        }
        $this->dispatch('toast', message: 'Team deleted.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'formTournamentId',
            'name',
            'email',
            'password',
            'walletBalance',
            'isLocked',
            'logo',
            'existingLogoPath',
            'primaryColor',
            'secondaryColor',
        ]);
        $this->walletBalance = 0;
        $this->isLocked = false;
    }

    public function mount(): void
    {
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.admin.teams.manager', [
            'tournaments' => Tournament::where('admin_id', auth()->id())->get(['id', 'name']),
            'teams' => Team::query()
                ->when($this->tournamentId > 0, fn ($query) => $query->where('tournament_id', $this->tournamentId))
                ->where('admin_id', auth()->id())
                ->with('user:id,email')
                ->latest()
                ->paginate(15),
        ]);
    }
}
