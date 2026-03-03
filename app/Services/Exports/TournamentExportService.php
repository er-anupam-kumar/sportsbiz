<?php

namespace App\Services\Exports;

use App\Models\Bid;
use App\Models\Player;
use App\Models\Team;
use App\Models\Tournament;
use Illuminate\Support\Facades\Storage;

class TournamentExportService
{
    public function exportCsv(Tournament $tournament): string
    {
        $path = 'exports/tournament_'.$tournament->id.'_'.now()->format('Ymd_His').'.csv';

        $rows = [
            ['Report', 'Tournament Summary'],
            ['Tournament', $tournament->name],
            ['Generated At', now()->toDateTimeString()],
            [],
            ['Metric', 'Value'],
            ['Total Teams', Team::where('tournament_id', $tournament->id)->count()],
            ['Total Players', Player::where('tournament_id', $tournament->id)->count()],
            ['Sold Players', Player::where('tournament_id', $tournament->id)->where('status', 'sold')->count()],
            ['Total Bids', Bid::where('tournament_id', $tournament->id)->count()],
        ];

        $content = collect($rows)
            ->map(fn (array $row) => collect($row)->map(fn ($value) => '"'.str_replace('"', '""', (string) $value).'"')->implode(','))
            ->implode(PHP_EOL);

        Storage::disk('local')->put($path, $content);

        return $path;
    }

    public function exportPdf(Tournament $tournament): string
    {
        $path = 'exports/tournament_'.$tournament->id.'_'.now()->format('Ymd_His').'.html';

        $payload = [
            'tournament' => $tournament,
            'teams' => Team::where('tournament_id', $tournament->id)->count(),
            'players' => Player::where('tournament_id', $tournament->id)->count(),
            'soldPlayers' => Player::where('tournament_id', $tournament->id)->where('status', 'sold')->count(),
            'bids' => Bid::where('tournament_id', $tournament->id)->count(),
        ];

        $html = view('exports.tournament-report', $payload)->render();
        Storage::disk('local')->put($path, $html);

        return $path;
    }

    public function exportExcel(Tournament $tournament): string
    {
        $path = 'exports/tournament_'.$tournament->id.'_'.now()->format('Ymd_His').'.xls';

        $teams = Team::where('tournament_id', $tournament->id)->count();
        $players = Player::where('tournament_id', $tournament->id)->count();
        $soldPlayers = Player::where('tournament_id', $tournament->id)->where('status', 'sold')->count();
        $bids = Bid::where('tournament_id', $tournament->id)->count();

        $xml = '<?xml version="1.0"?>'
            .'<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            .'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">'
            .'<Worksheet ss:Name="Report"><Table>'
            .'<Row><Cell><Data ss:Type="String">Metric</Data></Cell><Cell><Data ss:Type="String">Value</Data></Cell></Row>'
            .'<Row><Cell><Data ss:Type="String">Tournament</Data></Cell><Cell><Data ss:Type="String">'.e($tournament->name).'</Data></Cell></Row>'
            .'<Row><Cell><Data ss:Type="String">Total Teams</Data></Cell><Cell><Data ss:Type="Number">'.$teams.'</Data></Cell></Row>'
            .'<Row><Cell><Data ss:Type="String">Total Players</Data></Cell><Cell><Data ss:Type="Number">'.$players.'</Data></Cell></Row>'
            .'<Row><Cell><Data ss:Type="String">Sold Players</Data></Cell><Cell><Data ss:Type="Number">'.$soldPlayers.'</Data></Cell></Row>'
            .'<Row><Cell><Data ss:Type="String">Total Bids</Data></Cell><Cell><Data ss:Type="Number">'.$bids.'</Data></Cell></Row>'
            .'</Table></Worksheet></Workbook>';

        Storage::disk('local')->put($path, $xml);

        return $path;
    }
}
