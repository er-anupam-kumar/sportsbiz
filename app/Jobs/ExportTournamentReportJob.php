<?php

namespace App\Jobs;

use App\Models\AppNotification;
use App\Models\Tournament;
use App\Services\Exports\TournamentExportService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExportTournamentReportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $adminId,
        public int $tournamentId,
        public string $format,
    ) {
    }

    public function handle(TournamentExportService $exportService): void
    {
        $tournament = Tournament::query()
            ->whereKey($this->tournamentId)
            ->where('admin_id', $this->adminId)
            ->first();

        if (! $tournament) {
            return;
        }

        $filePath = match ($this->format) {
            'pdf' => $exportService->exportPdf($tournament),
            'excel' => $exportService->exportExcel($tournament),
            default => $exportService->exportCsv($tournament),
        };

        AppNotification::query()->create([
            'user_id' => $this->adminId,
            'tournament_id' => $tournament->id,
            'type' => 'export_ready',
            'title' => 'Report export ready',
            'message' => 'Your '.$this->format.' report is ready for download.',
            'data' => [
                'path' => $filePath,
                'format' => $this->format,
            ],
        ]);
    }
}
