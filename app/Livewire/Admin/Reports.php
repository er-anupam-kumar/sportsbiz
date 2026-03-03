<?php

namespace App\Livewire\Admin;

use App\Jobs\ExportTournamentReportJob;
use App\Models\AppNotification;
use App\Models\Bid;
use App\Models\Player;
use App\Models\Team;
use App\Models\TeamWalletTransaction;
use App\Models\Tournament;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Reports extends Component
{
    public int $tournamentId = 0;

    public function queueCsvExport(): void
    {
        $this->queueExport('csv');
    }

    public function queuePdfExport(): void
    {
        $this->queueExport('pdf');
    }

    public function queueExcelExport(): void
    {
        $this->queueExport('excel');
    }

    private function queueExport(string $format): void
    {
        $adminId = (int) auth()->id();
        $tournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) Tournament::where('admin_id', $adminId)->value('id');

        if (! $tournamentId) {
            return;
        }

        ExportTournamentReportJob::dispatch($adminId, $tournamentId, $format);
    }

    public function render()
    {
        $adminId = auth()->id();
        $tournaments = Tournament::where('admin_id', $adminId)->get(['id', 'name']);
        $tournamentIds = $tournaments->pluck('id');
        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) ($tournaments->first()?->id ?? 0);
        $activeTournament = $activeTournamentId > 0
            ? $tournaments->firstWhere('id', $activeTournamentId)
            : null;

        return view('livewire.admin.reports', [
            'tournaments' => $tournaments,
            'activeTournament' => $activeTournament,
            'bidCount' => Bid::whereIn('tournament_id', $tournamentIds)->count(),
            'walletDebits' => TeamWalletTransaction::whereIn('tournament_id', $tournamentIds)
                ->where('type', 'debit')
                ->sum('amount'),
            'activeTeams' => $activeTournamentId > 0
                ? Team::where('tournament_id', $activeTournamentId)
                    ->orderByDesc('squad_count')
                    ->limit(6)
                    ->get(['id', 'name', 'logo_path', 'primary_color', 'secondary_color', 'squad_count'])
                : collect(),
            'activeSoldPlayers' => $activeTournamentId > 0
                ? Player::where('tournament_id', $activeTournamentId)
                    ->where('status', 'sold')
                    ->latest('updated_at')
                    ->limit(6)
                    ->get(['id', 'name', 'image_path', 'final_price'])
                : collect(),
            'readyExports' => AppNotification::query()
                ->where('user_id', $adminId)
                ->where('type', 'export_ready')
                ->latest()
                ->limit(5)
                ->get(),
        ]);
    }
}
