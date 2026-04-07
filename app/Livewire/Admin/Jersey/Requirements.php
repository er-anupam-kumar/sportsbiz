<?php

namespace App\Livewire\Admin\Jersey;

use App\Models\Team;
use App\Models\TeamJerseyRequest;
use App\Models\Tournament;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Requirements extends Component
{
    use WithPagination;

    public int $tournamentId = 0;
    public bool $onlyAdditionalJersey = false;
    public ?int $selectedTeamId = null;

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyAdditionalJersey(): void
    {
        $this->resetPage();
    }

    public function viewDetails(int $teamId): void
    {
        $this->selectedTeamId = $teamId;
    }

    public function clearDetails(): void
    {
        $this->selectedTeamId = null;
    }

    public function toggleModule(): void
    {
        $adminId = (int) auth()->id();

        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) Tournament::query()->where('admin_id', $adminId)->value('id');

        if (! $activeTournamentId) {
            return;
        }

        $tournament = Tournament::query()
            ->where('admin_id', $adminId)
            ->find($activeTournamentId);

        if (! $tournament) {
            return;
        }

        $tournament->update([
            'jersey_module_enabled' => ! (bool) $tournament->jersey_module_enabled,
        ]);

        $this->dispatch('toast', message: 'Jersey module status updated.');
    }

    public function exportExcel(?int $teamId = null): StreamedResponse
    {
        $adminId = (int) auth()->id();

        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) Tournament::query()->where('admin_id', $adminId)->value('id');

        $entries = TeamJerseyRequest::query()
            ->where('admin_id', $adminId)
            ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
            ->when($this->onlyAdditionalJersey, fn ($query) => $query->where('additional_jersey_required', true))
            ->when($teamId, fn ($query) => $query->where('team_id', $teamId))
            ->with([
                'team:id,name,tournament_id',
                'tournament:id,name',
            ])
            ->orderBy('team_id')
            ->orderBy('request_for')
            ->orderBy('player_name')
            ->get();

        $xml = $this->buildExcelXml($entries);

        $filename = ($teamId ? 'team_jersey_requirements_' : 'jersey_requirements_').now()->format('Ymd_His').'.xls';

        return response()->streamDownload(function () use ($xml): void {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    public function exportPdf(int $teamId): StreamedResponse
    {
        $adminId = (int) auth()->id();

        $team = Team::query()
            ->where('admin_id', $adminId)
            ->findOrFail($teamId);

        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) Tournament::query()->where('admin_id', $adminId)->value('id');

        $entries = TeamJerseyRequest::query()
            ->where('admin_id', $adminId)
            ->where('team_id', $teamId)
            ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
            ->when($this->onlyAdditionalJersey, fn ($query) => $query->where('additional_jersey_required', true))
            ->orderBy('request_for')
            ->orderBy('player_name')
            ->get();

        $pdf = Pdf::loadView('exports.team-jersey-requirements', [
            'team' => $team,
            'entries' => $entries,
            'generatedAt' => now(),
            'onlyAdditionalJersey' => $this->onlyAdditionalJersey,
        ])->setPaper('a4', 'landscape');

        $filename = 'team_jersey_requirements_'.$teamId.'_'.now()->format('Ymd_His').'.pdf';

        return response()->streamDownload(function () use ($pdf): void {
            echo $pdf->output();
        }, $filename, [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function buildExcelXml($entries): string
    {
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $rows = [];
        $rows[] = '<Row><Cell><Data ss:Type="String">Team</Data></Cell><Cell><Data ss:Type="String">Tournament</Data></Cell><Cell><Data ss:Type="String">Type</Data></Cell><Cell><Data ss:Type="String">Name</Data></Cell><Cell><Data ss:Type="String">Size</Data></Cell><Cell><Data ss:Type="String">Nickname</Data></Cell><Cell><Data ss:Type="String">Jersey Number</Data></Cell><Cell><Data ss:Type="String">Additional Jersey</Data></Cell><Cell><Data ss:Type="String">Additional Quantity</Data></Cell><Cell><Data ss:Type="String">Submitted At</Data></Cell></Row>';

        foreach ($entries as $entry) {
            $name = $entry->request_for === 'staff'
                ? ($entry->staff_name ?: $entry->player_name)
                : $entry->player_name;

            $rows[] = '<Row>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ($entry->team?->name ?? '-')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ($entry->tournament?->name ?? '-')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ucfirst($entry->request_for ?? 'player')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) $name).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) $entry->size).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ($entry->nickname ?: '-')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) $entry->jersey_number).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.($entry->additional_jersey_required ? 'Yes' : 'No').'</Data></Cell>'
                .'<Cell><Data ss:Type="Number">'.(int) ($entry->additional_jersey_quantity ?? 0).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) optional($entry->created_at)->format('d M Y, h:i A')).'</Data></Cell>'
                .'</Row>';
        }

        return '<?xml version="1.0"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Jersey Requirements"><Table>'
            .implode('', $rows)
            .'</Table></Worksheet></Workbook>';
    }

    public function render()
    {
        $adminId = (int) auth()->id();

        $tournaments = Tournament::query()
            ->where('admin_id', $adminId)
            ->orderBy('name')
            ->get(['id', 'name', 'jersey_module_enabled']);

        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) ($tournaments->first()?->id ?? 0);

        $teams = Team::query()
            ->where('admin_id', $adminId)
            ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
            ->whereHas('jerseyRequests', function ($query) use ($activeTournamentId) {
                $query->when($activeTournamentId > 0, fn ($inner) => $inner->where('tournament_id', $activeTournamentId))
                    ->when($this->onlyAdditionalJersey, fn ($inner) => $inner->where('additional_jersey_required', true));
            })
            ->with('tournament:id,name')
            ->withCount([
                'jerseyRequests as requirements_count' => function ($query) use ($activeTournamentId) {
                    $query->when($activeTournamentId > 0, fn ($inner) => $inner->where('tournament_id', $activeTournamentId))
                        ->when($this->onlyAdditionalJersey, fn ($inner) => $inner->where('additional_jersey_required', true));
                },
                'jerseyRequests as additional_count' => function ($query) use ($activeTournamentId) {
                    $query->where('additional_jersey_required', true)
                        ->when($activeTournamentId > 0, fn ($inner) => $inner->where('tournament_id', $activeTournamentId));
                },
            ])
            ->latest('id')
            ->paginate(15);

        if ($this->selectedTeamId && ! $teams->pluck('id')->contains($this->selectedTeamId)) {
            $this->selectedTeamId = null;
        }

        $selectedTeamEntries = collect();
        if ($this->selectedTeamId) {
            $selectedTeamEntries = TeamJerseyRequest::query()
                ->where('admin_id', $adminId)
                ->where('team_id', $this->selectedTeamId)
                ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
                ->when($this->onlyAdditionalJersey, fn ($query) => $query->where('additional_jersey_required', true))
                ->latest()
                ->get();
        }

        return view('livewire.admin.jersey.requirements', [
            'tournaments' => $tournaments,
            'activeTournamentId' => $activeTournamentId,
            'teams' => $teams,
            'selectedTeamEntries' => $selectedTeamEntries,
            'onlyAdditionalJersey' => $this->onlyAdditionalJersey,
        ]);
    }
}
