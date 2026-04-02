<?php

namespace App\Livewire\Admin\Jersey;

use App\Models\TeamJerseyRequest;
use App\Models\Tournament;
use Illuminate\Support\Collection;
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

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function updatedOnlyAdditionalJersey(): void
    {
        $this->resetPage();
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

    public function exportExcel(): StreamedResponse
    {
        $adminId = (int) auth()->id();

        $activeTournamentId = $this->tournamentId > 0
            ? $this->tournamentId
            : (int) Tournament::query()->where('admin_id', $adminId)->value('id');

        $entries = TeamJerseyRequest::query()
            ->where('admin_id', $adminId)
            ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
            ->when($this->onlyAdditionalJersey, fn ($query) => $query->where('additional_jersey_required', true))
            ->with([
                'team:id,name,tournament_id',
                'tournament:id,name',
            ])
            ->orderBy('team_id')
            ->orderBy('player_name')
            ->get();

        $xml = $this->buildExcelXml($entries);

        $filename = 'jersey_requirements_'.now()->format('Ymd_His').'.xls';

        return response()->streamDownload(function () use ($xml): void {
            echo $xml;
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }

    private function buildExcelXml(Collection $entries): string
    {
        $escape = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');

        $rows = [];
        $rows[] = '<Row><Cell><Data ss:Type="String">Team</Data></Cell><Cell><Data ss:Type="String">Tournament</Data></Cell><Cell><Data ss:Type="String">Player</Data></Cell><Cell><Data ss:Type="String">Size</Data></Cell><Cell><Data ss:Type="String">Nickname</Data></Cell><Cell><Data ss:Type="String">Jersey Number</Data></Cell><Cell><Data ss:Type="String">Additional Jersey</Data></Cell><Cell><Data ss:Type="String">Additional Quantity</Data></Cell><Cell><Data ss:Type="String">Submitted At</Data></Cell></Row>';

        foreach ($entries as $entry) {
            $rows[] = '<Row>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ($entry->team?->name ?? '-')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) ($entry->tournament?->name ?? '-')).'</Data></Cell>'
                .'<Cell><Data ss:Type="String">'.$escape((string) $entry->player_name).'</Data></Cell>'
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

        $entries = TeamJerseyRequest::query()
            ->where('admin_id', $adminId)
            ->when($activeTournamentId > 0, fn ($query) => $query->where('tournament_id', $activeTournamentId))
            ->when($this->onlyAdditionalJersey, fn ($query) => $query->where('additional_jersey_required', true))
            ->with([
                'team:id,name,tournament_id,jersey_image_path',
                'tournament:id,name',
            ])
            ->latest()
            ->paginate(15);

        return view('livewire.admin.jersey.requirements', [
            'tournaments' => $tournaments,
            'activeTournamentId' => $activeTournamentId,
            'entries' => $entries,
            'onlyAdditionalJersey' => $this->onlyAdditionalJersey,
        ]);
    }
}
