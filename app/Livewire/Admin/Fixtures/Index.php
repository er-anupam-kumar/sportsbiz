<?php

namespace App\Livewire\Admin\Fixtures;

use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    public function render()
    {
        $adminId = (int) auth()->id();

        $tournaments = Tournament::query()
            ->where('admin_id', $adminId)
            ->withCount([
                'fixtures as fixtures_count',
                'fixtures as scheduled_count' => fn ($query) => $query->where('status', 'scheduled'),
                'fixtures as live_count' => fn ($query) => $query->where('status', 'live'),
                'fixtures as completed_count' => fn ($query) => $query->where('status', 'completed'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'status', 'starts_at']);

        return view('livewire.admin.fixtures.index', [
            'tournaments' => $tournaments,
        ]);
    }
}
