<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Payment;
use App\Models\Tournament;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.super-admin')]
class Reports extends Component
{
    public ?string $fromDate = null;
    public ?string $toDate = null;

    public function mount(): void
    {
        $this->fromDate = now()->startOfMonth()->format('Y-m-d');
        $this->toDate = now()->format('Y-m-d');
    }

    public function render()
    {
        $tournaments = Tournament::query()
            ->when($this->fromDate, fn ($query) => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('created_at', '<=', $this->toDate));

        $payments = Payment::query()
            ->when($this->fromDate, fn ($query) => $query->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('created_at', '<=', $this->toDate));

        return view('livewire.super-admin.reports', [
            'adminCount' => User::role('Admin')->count(),
            'tournamentCount' => (clone $tournaments)->count(),
            'revenue' => (clone $payments)->where('status', 'succeeded')->sum('amount'),
            'tournamentSummary' => (clone $tournaments)->selectRaw('status, count(*) as total')->groupBy('status')->get(),
            'paymentSummary' => (clone $payments)->selectRaw('provider, status, count(*) as total, sum(amount) as amount')->groupBy('provider', 'status')->get(),
        ]);
    }
}
