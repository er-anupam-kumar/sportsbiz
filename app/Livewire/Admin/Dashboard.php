<?php

namespace App\Livewire\Admin;

use App\Models\Tournament;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $adminId = auth()->id();

        return view('livewire.admin.dashboard', [
            'tournaments' => Tournament::where('admin_id', $adminId)->count(),
            'teams' => User::where('parent_admin_id', $adminId)->role('Team')->count(),
        ]);
    }
}
