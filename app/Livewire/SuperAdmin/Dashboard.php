<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Payment;
use App\Models\Tournament;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.super-admin')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.super-admin.dashboard', [
            'adminCount' => User::role('Admin')->count(),
            'activeTournaments' => Tournament::whereIn('status', ['active', 'paused'])->count(),
            'successfulPayments' => Payment::where('status', 'succeeded')->sum('amount'),
        ]);
    }
}
