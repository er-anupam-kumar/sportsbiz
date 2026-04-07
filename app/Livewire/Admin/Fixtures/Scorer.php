<?php

namespace App\Livewire\Admin\Fixtures;

use App\Models\Fixture;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Scorer extends Component
{
    public Fixture $fixture;
    public bool $isCricket = false;

    public int $homePoints = 0;
    public int $awayPoints = 0;
    public string $resultText = '';
    public string $progressNote = '';

    public int $currentInnings = 1;
    public array $innings = [];
    public string $striker = '';
    public string $nonStriker = '';
    public string $bowler = '';
    public ?int $targetRuns = null;
    public string $manualEvent = '';
    public array $recentEvents = [];
    public array $ballHistory = [];
    public array $overBreakdown = [];
    public array $partnerships = [];

    public bool $showGoLiveModal = false;
    public int $tossWinnerTeamId = 0;
    public string $tossDecision = 'bat';
    public int $battingTeamId = 0;
    public int $bowlingTeamId = 0;
    public int $strikerPlayerId = 0;
    public int $nonStrikerPlayerId = 0;
    public int $bowlerPlayerId = 0;

    public int $deliveryRunsOffBat = 0;
    public string $deliveryExtraType = 'none';
    public int $deliveryExtraRuns = 0;
    public bool $deliveryWicket = false;
    public bool $showWicketModal = false;
    public int $wicketOutPlayerId = 0;
    public string $wicketType = 'bowled';

    private function scorerLocked(): bool
    {
        return (string) $this->fixture->status === 'completed';
    }

    private function rejectIfLocked(): bool
    {
        if (! $this->scorerLocked()) {
            return false;
        }

        $this->dispatch('toast', message: 'Scorer is locked for completed match. Change status to Live/Scheduled to edit.');

        return true;
    }

    public function mount(Fixture $fixture): void
    {
        $fixture->load(['tournament.sport', 'homeTeam:id,name', 'awayTeam:id,name']);
        abort_unless((int) $fixture->admin_id === (int) auth()->id(), 403);

        $this->fixture = $fixture;
        $this->isCricket = $this->detectCricket();

        if ($this->isCricket) {
            $this->bootCricketState();
            return;
        }

        $this->homePoints = (int) ($fixture->home_points ?? 0);
        $this->awayPoints = (int) ($fixture->away_points ?? 0);
        $this->resultText = (string) ($fixture->result_text ?? '');
        $this->progressNote = (string) ($fixture->notes ?? '');
    }

    private function detectCricket(): bool
    {
        $sport = $this->fixture->tournament?->sport;
        $sportText = Str::lower(trim((string) ($sport?->slug ?: $sport?->name ?: '')));

        return Str::contains($sportText, 'cricket');
    }

    private function bootCricketState(): void
    {
        $payload = (array) ($this->fixture->score_payload ?? []);

        $defaultInnings = [
            1 => [
                'batting_team_id' => (int) ($this->fixture->home_team_id ?? 0),
                'runs' => 0,
                'wickets' => 0,
                'overs' => 0,
                'balls' => 0,
                'extras' => 0,
            ],
            2 => [
                'batting_team_id' => (int) ($this->fixture->away_team_id ?? 0),
                'runs' => 0,
                'wickets' => 0,
                'overs' => 0,
                'balls' => 0,
                'extras' => 0,
            ],
        ];

        $this->innings = $payload['innings'] ?? $defaultInnings;
        $this->currentInnings = (int) ($payload['current_innings'] ?? $this->fixture->current_innings ?? 1);
        $meta = (array) ($payload['meta'] ?? []);
        $this->striker = (string) ($meta['striker'] ?? '');
        $this->nonStriker = (string) ($meta['non_striker'] ?? '');
        $this->bowler = (string) ($meta['bowler'] ?? '');
        $this->targetRuns = isset($meta['target_runs']) ? (int) $meta['target_runs'] : null;
        $this->recentEvents = array_values((array) ($meta['recent_events'] ?? []));
        $this->ballHistory = array_values((array) ($meta['ball_history'] ?? []));
        $this->overBreakdown = (array) ($meta['over_breakdown'] ?? []);
        $this->partnerships = (array) ($meta['partnerships'] ?? []);
        $this->tossWinnerTeamId = (int) ($meta['toss']['winner_team_id'] ?? 0);
        $this->tossDecision = (string) ($meta['toss']['decision'] ?? 'bat');
        $this->battingTeamId = (int) ($meta['lineup']['batting_team_id'] ?? 0);
        $this->bowlingTeamId = (int) ($meta['lineup']['bowling_team_id'] ?? 0);
        $this->strikerPlayerId = (int) ($meta['lineup']['striker_player_id'] ?? 0);
        $this->nonStrikerPlayerId = (int) ($meta['lineup']['non_striker_player_id'] ?? 0);
        $this->bowlerPlayerId = (int) ($meta['lineup']['bowler_player_id'] ?? 0);
        $this->resultText = (string) ($this->fixture->result_text ?? '');
        $this->progressNote = (string) ($this->fixture->notes ?? '');

        foreach ([1, 2] as $inning) {
            if (! isset($this->overBreakdown[$inning])) {
                $this->overBreakdown[$inning] = [];
            }
            if (! isset($this->partnerships[$inning])) {
                $this->partnerships[$inning] = [
                    'current_runs' => 0,
                    'current_balls' => 0,
                    'stands' => [],
                ];
            }
        }

        if ($this->battingTeamId <= 0 && $this->fixture->home_team_id) {
            $this->battingTeamId = (int) $this->fixture->home_team_id;
        }
        if ($this->bowlingTeamId <= 0 && $this->fixture->away_team_id) {
            $this->bowlingTeamId = (int) $this->fixture->away_team_id;
        }
    }

    public function updatedTossWinnerTeamId(): void
    {
        $this->syncBatBowlFromToss();
    }

    public function updatedTossDecision(): void
    {
        $this->syncBatBowlFromToss();
    }

    public function updatedBattingTeamId(): void
    {
        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        if ($this->battingTeamId === $homeId) {
            $this->bowlingTeamId = $awayId;
        } elseif ($this->battingTeamId === $awayId) {
            $this->bowlingTeamId = $homeId;
        }

        $this->strikerPlayerId = 0;
        $this->nonStrikerPlayerId = 0;
        $this->bowlerPlayerId = 0;
    }

    private function syncBatBowlFromToss(): void
    {
        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        if (! in_array($this->tossWinnerTeamId, [$homeId, $awayId], true)) {
            return;
        }

        $other = $this->tossWinnerTeamId === $homeId ? $awayId : $homeId;

        if ($this->tossDecision === 'bat') {
            $this->battingTeamId = $this->tossWinnerTeamId;
            $this->bowlingTeamId = $other;
        } else {
            $this->battingTeamId = $other;
            $this->bowlingTeamId = $this->tossWinnerTeamId;
        }

        $this->strikerPlayerId = 0;
        $this->nonStrikerPlayerId = 0;
        $this->bowlerPlayerId = 0;
    }

    public function setMatchStatus(string $status): void
    {
        if (! in_array($status, ['scheduled', 'live', 'completed', 'postponed', 'cancelled'], true)) {
            return;
        }

        $payload = ['status' => $status];
        if ($status === 'completed') {
            $generated = $this->generateAutoResult();
            if ($generated !== null) {
                $payload['result_text'] = $generated;
                $this->resultText = $generated;
            }

            $outcome = $this->resolveOutcomeTeamIds();
            $payload['winner_team_id'] = $outcome['winner_team_id'];
            $payload['loser_team_id'] = $outcome['loser_team_id'];
        } else {
            $payload['winner_team_id'] = null;
            $payload['loser_team_id'] = null;
        }

        $this->fixture->update($payload);
        $this->fixture->refresh();
        $this->syncDependentFixturesFromOutcome();
        $this->dispatch('toast', message: 'Match status updated to '.strtoupper($status).'.');
    }

    private function syncDependentFixturesFromOutcome(): void
    {
        $sourceFixture = Fixture::query()
            ->whereKey($this->fixture->id)
            ->first();

        if (! $sourceFixture) {
            return;
        }

        $winnerId = (int) ($sourceFixture->winner_team_id ?? 0);
        $loserId = (int) ($sourceFixture->loser_team_id ?? 0);

        $children = Fixture::query()
            ->where('admin_id', (int) auth()->id())
            ->where('tournament_id', (int) $this->fixture->tournament_id)
            ->where('status', '!=', 'completed')
            ->where(function ($query) use ($sourceFixture) {
                $query->where('home_source_fixture_id', $sourceFixture->id)
                    ->orWhere('away_source_fixture_id', $sourceFixture->id);
            })
            ->get();

        foreach ($children as $child) {
            $update = [];

            if ((int) $child->home_source_fixture_id === (int) $sourceFixture->id) {
                if ((string) $child->home_source_type === 'winner_of') {
                    $update['home_team_id'] = $winnerId > 0 ? $winnerId : null;
                } elseif ((string) $child->home_source_type === 'loser_of') {
                    $update['home_team_id'] = $loserId > 0 ? $loserId : null;
                }

                if (array_key_exists('home_team_id', $update)) {
                    $update['home_slot_label'] = $update['home_team_id'] ? null : (((string) $child->home_source_type === 'winner_of' ? 'Winner of ' : 'Loser of ').$sourceFixture->display_label);
                }
            }

            if ((int) $child->away_source_fixture_id === (int) $sourceFixture->id) {
                if ((string) $child->away_source_type === 'winner_of') {
                    $update['away_team_id'] = $winnerId > 0 ? $winnerId : null;
                } elseif ((string) $child->away_source_type === 'loser_of') {
                    $update['away_team_id'] = $loserId > 0 ? $loserId : null;
                }

                if (array_key_exists('away_team_id', $update)) {
                    $update['away_slot_label'] = $update['away_team_id'] ? null : (((string) $child->away_source_type === 'winner_of' ? 'Winner of ' : 'Loser of ').$sourceFixture->display_label);
                }
            }

            if (! empty($update)) {
                Fixture::query()->whereKey((int) $child->id)->update($update);
            }
        }
    }

    public function openGoLiveModal(): void
    {
        if (! $this->isCricket) {
            $this->setMatchStatus('live');
            return;
        }

        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        if ($this->tossWinnerTeamId <= 0) {
            $this->tossWinnerTeamId = $homeId;
        }
        if ($this->battingTeamId <= 0) {
            $this->battingTeamId = $homeId;
        }
        if ($this->bowlingTeamId <= 0) {
            $this->bowlingTeamId = $awayId;
        }

        $this->showGoLiveModal = true;
    }

    public function openWicketModal(): void
    {
        if ($this->rejectIfLocked() || ! $this->isCricket) {
            return;
        }

        $this->wicketOutPlayerId = 0;
        $this->wicketType = 'bowled';
        $this->showWicketModal = true;
    }

    public function confirmWicketEvent(): void
    {
        if ($this->rejectIfLocked() || ! $this->isCricket) {
            return;
        }

        $battingTeamId = (int) ($this->innings[$this->currentInnings]['batting_team_id'] ?? 0);

        $this->validate([
            'wicketOutPlayerId' => ['required', 'integer', 'min:1'],
            'wicketType' => ['required', 'in:bowled,caught,lbw,run_out,stumped,hit_wicket,retired_out,other'],
        ]);

        $playerExists = Player::query()
            ->where('tournament_id', (int) $this->fixture->tournament_id)
            ->where('sold_team_id', $battingTeamId)
            ->whereKey($this->wicketOutPlayerId)
            ->exists();

        if (! $playerExists) {
            $this->addError('wicketOutPlayerId', 'Dismissed player must belong to current batting team.');
            return;
        }

        $this->registerDelivery(
            (int) $this->currentInnings,
            0,
            'none',
            0,
            true,
            $this->wicketOutPlayerId,
            $this->wicketType,
        );

        $this->showWicketModal = false;
        $this->wicketOutPlayerId = 0;
        $this->wicketType = 'bowled';
    }

    public function confirmGoLiveSetup(): void
    {
        if (! $this->isCricket) {
            $this->setMatchStatus('live');
            return;
        }

        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        $this->validate([
            'tossWinnerTeamId' => ['required', 'integer', 'in:'.$homeId.','.$awayId],
            'tossDecision' => ['required', 'in:bat,bowl'],
            'battingTeamId' => ['required', 'integer', 'in:'.$homeId.','.$awayId],
            'bowlingTeamId' => ['required', 'integer', 'in:'.$homeId.','.$awayId],
            'strikerPlayerId' => ['required', 'integer', 'min:1'],
            'nonStrikerPlayerId' => ['required', 'integer', 'min:1'],
            'bowlerPlayerId' => ['required', 'integer', 'min:1'],
        ]);

        if ($this->battingTeamId === $this->bowlingTeamId) {
            $this->addError('bowlingTeamId', 'Batting and bowling teams must be different.');
            return;
        }

        if ($this->strikerPlayerId === $this->nonStrikerPlayerId) {
            $this->addError('nonStrikerPlayerId', 'Striker and non-striker must be different.');
            return;
        }

        if (! isset($this->innings[1])) {
            $this->innings[1] = ['batting_team_id' => $this->battingTeamId, 'runs' => 0, 'wickets' => 0, 'overs' => 0, 'balls' => 0, 'extras' => 0];
        }
        if (! isset($this->innings[2])) {
            $this->innings[2] = ['batting_team_id' => $this->bowlingTeamId, 'runs' => 0, 'wickets' => 0, 'overs' => 0, 'balls' => 0, 'extras' => 0];
        }

        $this->innings[1]['batting_team_id'] = $this->battingTeamId;
        $this->innings[2]['batting_team_id'] = $this->bowlingTeamId;
        $this->currentInnings = 1;

        $this->striker = (string) ($this->playerNameById($this->strikerPlayerId) ?? '');
        $this->nonStriker = (string) ($this->playerNameById($this->nonStrikerPlayerId) ?? '');
        $this->bowler = (string) ($this->playerNameById($this->bowlerPlayerId) ?? '');

        $this->saveCricket();
        $this->setMatchStatus('live');
        $this->showGoLiveModal = false;
    }

    private function playerNameById(int $playerId): ?string
    {
        if ($playerId <= 0) {
            return null;
        }

        return Player::query()->whereKey($playerId)->value('name');
    }

    public function completeAndLock(): void
    {
        $this->setMatchStatus('completed');
    }

    public function addPoint(string $team): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if ($this->isCricket) {
            return;
        }

        if ($team === 'home') {
            $this->homePoints++;
        }
        if ($team === 'away') {
            $this->awayPoints++;
        }

        if ($this->fixture->status === 'scheduled') {
            $this->setMatchStatus('live');
        }

        $this->saveNonCricket();
    }

    public function saveNonCricket(): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if ($this->isCricket) {
            return;
        }

        $this->validate([
            'homePoints' => ['required', 'integer', 'min:0'],
            'awayPoints' => ['required', 'integer', 'min:0'],
            'resultText' => ['nullable', 'string', 'max:255'],
            'progressNote' => ['nullable', 'string', 'max:2000'],
        ]);

        $resultText = $this->resultText ?: null;
        $outcome = ['winner_team_id' => null, 'loser_team_id' => null];
        if ($this->fixture->status === 'completed') {
            $resultText = $this->generateAutoResult($this->homePoints, $this->awayPoints) ?: $resultText;
            $this->resultText = (string) ($resultText ?? '');
            $outcome = $this->resolveOutcomeTeamIds($this->homePoints, $this->awayPoints);
        }

        $this->fixture->update([
            'home_points' => $this->homePoints,
            'away_points' => $this->awayPoints,
            'winner_team_id' => $outcome['winner_team_id'],
            'loser_team_id' => $outcome['loser_team_id'],
            'result_text' => $resultText,
            'notes' => $this->progressNote ?: null,
            'score_payload' => null,
            'current_innings' => null,
        ]);

        $this->fixture->refresh();
        $this->dispatch('toast', message: 'Points updated.');
    }

    public function addCricketEvent(int $runs = 0, bool $isWicket = false, string $extraType = 'none'): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! $this->isCricket) {
            return;
        }

        $extraRuns = in_array($extraType, ['wide', 'no_ball'], true) ? 1 : 0;
        $this->registerDelivery((int) $this->currentInnings, max(0, $runs), $extraType, $extraRuns, $isWicket);
    }

    public function addCustomDelivery(): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! $this->isCricket) {
            return;
        }

        $this->validate([
            'deliveryRunsOffBat' => ['required', 'integer', 'min:0', 'max:12'],
            'deliveryExtraType' => ['required', 'in:none,wide,no_ball,bye,leg_bye'],
            'deliveryExtraRuns' => ['required', 'integer', 'min:0', 'max:12'],
            'deliveryWicket' => ['boolean'],
        ]);

        $this->registerDelivery(
            (int) $this->currentInnings,
            (int) $this->deliveryRunsOffBat,
            (string) $this->deliveryExtraType,
            (int) $this->deliveryExtraRuns,
            (bool) $this->deliveryWicket,
            $this->deliveryWicket ? (int) $this->wicketOutPlayerId : null,
            $this->deliveryWicket ? (string) $this->wicketType : null,
        );

        $this->deliveryRunsOffBat = 0;
        $this->deliveryExtraType = 'none';
        $this->deliveryExtraRuns = 0;
        $this->deliveryWicket = false;
    }

    private function registerDelivery(
        int $inningsKey,
        int $runsOffBat,
        string $extraType,
        int $extraRuns,
        bool $isWicket,
        ?int $outPlayerId = null,
        ?string $wicketType = null,
    ): void
    {
        if ($inningsKey <= 0 || ! isset($this->innings[$inningsKey])) {
            return;
        }

        $runsOffBat = max(0, $runsOffBat);
        $extraRuns = max(0, $extraRuns);

        if (in_array($extraType, ['wide', 'no_ball'], true)) {
            $extraRuns = max(1, $extraRuns);
        }

        $isLegalDelivery = ! in_array($extraType, ['wide', 'no_ball'], true);

        if (! isset($this->innings[$inningsKey])) {
            return;
        }

        $innings = $this->innings[$inningsKey];
        $before = $innings;
        $beforeOver = $this->overBreakdown[$inningsKey] ?? [];
        $beforePartnership = $this->partnerships[$inningsKey] ?? ['current_runs' => 0, 'current_balls' => 0, 'stands' => []];
        $beforeStrikerPlayerId = $this->strikerPlayerId;
        $beforeNonStrikerPlayerId = $this->nonStrikerPlayerId;
        $beforeStriker = $this->striker;
        $beforeNonStriker = $this->nonStriker;

        $isExtra = $extraType !== 'none';
        $totalRuns = $runsOffBat + $extraRuns;

        $innings['runs'] = (int) ($innings['runs'] ?? 0) + $totalRuns;

        if ($isExtra) {
            $innings['extras'] = (int) ($innings['extras'] ?? 0) + $extraRuns;
        }

        if ($isLegalDelivery) {
            $balls = (int) ($innings['balls'] ?? 0) + 1;
            $overs = (int) ($innings['overs'] ?? 0);
            if ($balls >= 6) {
                $overs++;
                $balls = 0;
            }
            $innings['overs'] = $overs;
            $innings['balls'] = $balls;
        }

        if ($isWicket) {
            $innings['wickets'] = min(10, (int) ($innings['wickets'] ?? 0) + 1);
        }

        $this->innings[$inningsKey] = $innings;

        // Rotate strike when total runs taken from the ball are odd.
        if (($totalRuns % 2) === 1) {
            $this->swapStrikers();
        }

        $overCompleted = $isLegalDelivery && ((int) ($before['balls'] ?? 0) === 5);
        if ($overCompleted) {
            $this->swapStrikers();
        }

        $parts = [];
        if ($isExtra) {
            $parts[] = strtoupper(str_replace('_', '-', $extraType)).' '.$extraRuns;
        }
        if ($runsOffBat > 0) {
            $parts[] = $runsOffBat.' run'.($runsOffBat > 1 ? 's' : '').' off bat';
        }
        if ($isWicket) {
            $wType = strtoupper(str_replace('_', ' ', (string) ($wicketType ?: 'wicket')));
            $outName = $outPlayerId ? $this->playerNameById($outPlayerId) : null;
            $parts[] = 'WICKET'.($outName ? ' - '.$outName : '').' ('.$wType.')';
        }
        if ($runsOffBat === 0 && $extraRuns === 0 && ! $isWicket) {
            $parts[] = 'DOT BALL';
        }

        $beforeOver = (int) ($before['overs'] ?? 0);
        $beforeBall = (int) ($before['balls'] ?? 0);
        $displayBall = $isLegalDelivery ? ($beforeBall + 1) : $beforeBall;
        $overLabel = $beforeOver.'.'.$displayBall;
        $eventText = 'Over '.$overLabel.': '.implode(' + ', $parts);
        $this->updateOverBreakdown($inningsKey, $before, $totalRuns, $isWicket, $isLegalDelivery);
        $this->updatePartnership($inningsKey, $totalRuns, $isWicket, $isLegalDelivery);

        array_unshift($this->recentEvents, $eventText);
        $this->recentEvents = array_slice($this->recentEvents, 0, 18);

        array_unshift($this->ballHistory, [
            'inning' => $inningsKey,
            'before' => $before,
            'before_over_breakdown' => $beforeOver,
            'before_partnership' => $beforePartnership,
            'before_striker_player_id' => $beforeStrikerPlayerId,
            'before_non_striker_player_id' => $beforeNonStrikerPlayerId,
            'before_striker' => $beforeStriker,
            'before_non_striker' => $beforeNonStriker,
            'event_text' => $eventText,
            'runs_off_bat' => $runsOffBat,
            'extra_runs' => $extraRuns,
            'is_wicket' => $isWicket,
            'extra_type' => $extraType,
            'legal_ball' => $isLegalDelivery,
            'striker_player_id' => $this->strikerPlayerId ?: null,
            'non_striker_player_id' => $this->nonStrikerPlayerId ?: null,
            'bowler_player_id' => $this->bowlerPlayerId ?: null,
            'out_player_id' => $outPlayerId,
            'wicket_type' => $wicketType,
            'at' => now()->toDateTimeString(),
        ]);
        $this->ballHistory = array_slice($this->ballHistory, 0, 120);

        if ($this->fixture->status === 'scheduled') {
            $this->fixture->update(['status' => 'live']);
            $this->fixture->refresh();
        }

        $secondInningsFinished = false;
        if ($inningsKey === 2) {
            $in2Runs = (int) ($this->innings[2]['runs'] ?? 0);
            $in2Wickets = (int) ($this->innings[2]['wickets'] ?? 0);
            $firstInningsRuns = (int) ($this->innings[1]['runs'] ?? 0);
            $target = (int) ($this->targetRuns ?: ($firstInningsRuns > 0 ? ($firstInningsRuns + 1) : 0));

            $isAllOut = $in2Wickets >= 10;
            $targetReached = $target > 0 && $in2Runs >= $target;

            $secondInningsFinished = $isAllOut || $targetReached;
        }

        $this->saveCricket();

        if ($secondInningsFinished && (string) $this->fixture->status !== 'completed') {
            $this->setMatchStatus('completed');
            $this->dispatch('toast', message: 'Second innings ended. Match auto-completed.');
        }
    }

    private function swapStrikers(): void
    {
        $tmpId = $this->strikerPlayerId;
        $this->strikerPlayerId = $this->nonStrikerPlayerId;
        $this->nonStrikerPlayerId = $tmpId;

        $tmpName = $this->striker;
        $this->striker = $this->nonStriker;
        $this->nonStriker = $tmpName;
    }

    public function undoLastBall(): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! $this->isCricket || empty($this->ballHistory)) {
            return;
        }

        $last = array_shift($this->ballHistory);
        $inning = (int) ($last['inning'] ?? 0);

        if ($inning <= 0 || ! isset($this->innings[$inning])) {
            return;
        }

        $this->innings[$inning] = (array) ($last['before'] ?? $this->innings[$inning]);
        $this->overBreakdown[$inning] = (array) ($last['before_over_breakdown'] ?? ($this->overBreakdown[$inning] ?? []));
        $this->partnerships[$inning] = (array) ($last['before_partnership'] ?? ($this->partnerships[$inning] ?? ['current_runs' => 0, 'current_balls' => 0, 'stands' => []]));
        $this->strikerPlayerId = (int) ($last['before_striker_player_id'] ?? $this->strikerPlayerId);
        $this->nonStrikerPlayerId = (int) ($last['before_non_striker_player_id'] ?? $this->nonStrikerPlayerId);
        $this->striker = (string) ($last['before_striker'] ?? $this->striker);
        $this->nonStriker = (string) ($last['before_non_striker'] ?? $this->nonStriker);

        $eventText = (string) ($last['event_text'] ?? '');
        if ($eventText !== '') {
            $idx = array_search($eventText, $this->recentEvents, true);
            if ($idx !== false) {
                unset($this->recentEvents[$idx]);
                $this->recentEvents = array_values($this->recentEvents);
            }
        }

        $this->saveCricket();
        $this->dispatch('toast', message: 'Last ball undone.');
    }

    public function switchInnings(int $innings): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! in_array($innings, [1, 2], true)) {
            return;
        }

        $this->currentInnings = $innings;
        $this->saveCricket();
    }

    public function pushManualEvent(): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! $this->isCricket) {
            return;
        }

        $event = trim($this->manualEvent);
        if ($event === '') {
            return;
        }

        array_unshift($this->recentEvents, $event);
        $this->recentEvents = array_slice($this->recentEvents, 0, 18);
        $this->manualEvent = '';
        $this->saveCricket();
    }

    private function updateOverBreakdown(int $inning, array $beforeInnings, int $totalRuns, bool $isWicket, bool $isLegalDelivery): void
    {
        $overNumber = (int) ($beforeInnings['overs'] ?? 0);
        $overKey = (string) $overNumber;

        $over = $this->overBreakdown[$inning][$overKey] ?? [
            'runs' => 0,
            'wickets' => 0,
            'legal_balls' => 0,
        ];

        $over['runs'] += $totalRuns;
        if ($isWicket) {
            $over['wickets'] += 1;
        }
        if ($isLegalDelivery) {
            $over['legal_balls'] += 1;
        }

        $this->overBreakdown[$inning][$overKey] = $over;
        ksort($this->overBreakdown[$inning]);
    }

    private function updatePartnership(int $inning, int $totalRuns, bool $isWicket, bool $isLegalDelivery): void
    {
        $state = $this->partnerships[$inning] ?? [
            'current_runs' => 0,
            'current_balls' => 0,
            'stands' => [],
        ];

        $state['current_runs'] = (int) ($state['current_runs'] ?? 0) + $totalRuns;
        if ($isLegalDelivery) {
            $state['current_balls'] = (int) ($state['current_balls'] ?? 0) + 1;
        }

        if ($isWicket) {
            $state['stands'][] = [
                'runs' => (int) $state['current_runs'],
                'balls' => (int) $state['current_balls'],
                'at_wicket' => (int) ($this->innings[$inning]['wickets'] ?? 0),
            ];
            $state['current_runs'] = 0;
            $state['current_balls'] = 0;
        }

        $this->partnerships[$inning] = $state;
    }

    private function generateAutoResult(?int $homePoints = null, ?int $awayPoints = null): ?string
    {
        if (! $this->isCricket) {
            $home = (int) ($homePoints ?? $this->homePoints);
            $away = (int) ($awayPoints ?? $this->awayPoints);

            if ($home === $away) {
                return 'Match tied at '.$home.'-'.$away.'.';
            }

            $winner = $home > $away ? $this->fixture->home_display_name : $this->fixture->away_display_name;
            $margin = abs($home - $away);

            return $winner.' won by '.$margin.' point'.($margin > 1 ? 's' : '').'.';
        }

        $innings = $this->innings;
        $in1 = (array) ($innings[1] ?? []);
        $in2 = (array) ($innings[2] ?? []);

        $firstTeamId = (int) ($in1['batting_team_id'] ?? 0);
        $secondTeamId = (int) ($in2['batting_team_id'] ?? 0);
        $firstRuns = (int) ($in1['runs'] ?? 0);
        $secondRuns = (int) ($in2['runs'] ?? 0);
        $secondWickets = (int) ($in2['wickets'] ?? 0);

        $teamNameById = [
            (int) ($this->fixture->homeTeam?->id ?? 0) => $this->fixture->home_display_name,
            (int) ($this->fixture->awayTeam?->id ?? 0) => $this->fixture->away_display_name,
        ];

        if ($firstRuns === 0 && $secondRuns === 0) {
            return null;
        }

        if ($secondRuns > $firstRuns && $secondTeamId > 0) {
            $wicketsLeft = max(0, 10 - $secondWickets);
            return ($teamNameById[$secondTeamId] ?? 'Team').' won by '.$wicketsLeft.' wicket'.($wicketsLeft !== 1 ? 's' : '').'.';
        }

        if ($firstRuns > $secondRuns && $firstTeamId > 0) {
            $margin = $firstRuns - $secondRuns;
            return ($teamNameById[$firstTeamId] ?? 'Team').' won by '.$margin.' run'.($margin !== 1 ? 's' : '').'.';
        }

        if ($firstRuns === $secondRuns) {
            return 'Match tied.';
        }

        return null;
    }

    private function resolveOutcomeTeamIds(?int $homePoints = null, ?int $awayPoints = null): array
    {
        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        if (! $this->isCricket) {
            $home = (int) ($homePoints ?? $this->homePoints);
            $away = (int) ($awayPoints ?? $this->awayPoints);

            if ($home > $away) {
                return ['winner_team_id' => $homeId, 'loser_team_id' => $awayId];
            }
            if ($away > $home) {
                return ['winner_team_id' => $awayId, 'loser_team_id' => $homeId];
            }

            return ['winner_team_id' => null, 'loser_team_id' => null];
        }

        $in1 = (array) ($this->innings[1] ?? []);
        $in2 = (array) ($this->innings[2] ?? []);

        $firstTeamId = (int) ($in1['batting_team_id'] ?? 0);
        $secondTeamId = (int) ($in2['batting_team_id'] ?? 0);
        $firstRuns = (int) ($in1['runs'] ?? 0);
        $secondRuns = (int) ($in2['runs'] ?? 0);

        if ($firstRuns > $secondRuns) {
            return ['winner_team_id' => $firstTeamId ?: null, 'loser_team_id' => $secondTeamId ?: null];
        }
        if ($secondRuns > $firstRuns) {
            return ['winner_team_id' => $secondTeamId ?: null, 'loser_team_id' => $firstTeamId ?: null];
        }

        return ['winner_team_id' => null, 'loser_team_id' => null];
    }

    private function buildLiveScorecardTables(array $playersByTeam): array
    {
        $inning = (int) $this->currentInnings;
        $battingTeamId = (int) ($this->innings[$inning]['batting_team_id'] ?? 0);
        if ($battingTeamId <= 0) {
            return ['batters' => [], 'bowlers' => []];
        }

        $bowlingTeamId = $battingTeamId === (int) $this->fixture->home_team_id
            ? (int) $this->fixture->away_team_id
            : (int) $this->fixture->home_team_id;

        $nameById = [];
        foreach (($playersByTeam[$battingTeamId] ?? collect()) as $p) {
            $nameById[(int) $p->id] = (string) $p->name;
        }
        foreach (($playersByTeam[$bowlingTeamId] ?? collect()) as $p) {
            $nameById[(int) $p->id] = (string) $p->name;
        }

        $batters = [];
        $bowlers = [];

        $dismissalText = static function (?string $type): string {
            $t = (string) ($type ?? 'out');
            return strtoupper(str_replace('_', ' ', $t));
        };

        foreach (array_reverse($this->ballHistory) as $ball) {
            if ((int) ($ball['inning'] ?? 0) !== $inning) {
                continue;
            }

            $strikerId = (int) ($ball['striker_player_id'] ?? 0);
            $bowlerId = (int) ($ball['bowler_player_id'] ?? 0);
            $runsOffBat = (int) ($ball['runs_off_bat'] ?? 0);
            $extraRuns = (int) ($ball['extra_runs'] ?? 0);
            $extraType = (string) ($ball['extra_type'] ?? 'none');
            $isLegal = (bool) ($ball['legal_ball'] ?? false);
            $isWicket = (bool) ($ball['is_wicket'] ?? false);
            $outPlayerId = (int) ($ball['out_player_id'] ?? 0);
            $wicketType = (string) ($ball['wicket_type'] ?? '');

            if ($strikerId > 0) {
                if (! isset($batters[$strikerId])) {
                    $batters[$strikerId] = [
                        'name' => $nameById[$strikerId] ?? ('Player #'.$strikerId),
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'out' => false,
                        'dismissal' => null,
                    ];
                }

                $batters[$strikerId]['runs'] += $runsOffBat;
                if ($isLegal) {
                    $batters[$strikerId]['balls'] += 1;
                }
                if ($runsOffBat === 4) {
                    $batters[$strikerId]['fours'] += 1;
                }
                if ($runsOffBat === 6) {
                    $batters[$strikerId]['sixes'] += 1;
                }
            }

            if ($outPlayerId > 0) {
                if (! isset($batters[$outPlayerId])) {
                    $batters[$outPlayerId] = [
                        'name' => $nameById[$outPlayerId] ?? ('Player #'.$outPlayerId),
                        'runs' => 0,
                        'balls' => 0,
                        'fours' => 0,
                        'sixes' => 0,
                        'out' => true,
                        'dismissal' => $dismissalText($wicketType),
                    ];
                } else {
                    $batters[$outPlayerId]['out'] = true;
                    $batters[$outPlayerId]['dismissal'] = $dismissalText($wicketType);
                }
            }

            if ($bowlerId > 0) {
                if (! isset($bowlers[$bowlerId])) {
                    $bowlers[$bowlerId] = [
                        'name' => $nameById[$bowlerId] ?? ('Player #'.$bowlerId),
                        'balls' => 0,
                        'runs' => 0,
                        'wickets' => 0,
                        'wides' => 0,
                        'no_balls' => 0,
                    ];
                }

                $creditedExtras = in_array($extraType, ['wide', 'no_ball'], true) ? $extraRuns : 0;
                $bowlers[$bowlerId]['runs'] += ($runsOffBat + $creditedExtras);
                if ($isLegal) {
                    $bowlers[$bowlerId]['balls'] += 1;
                }
                if ($extraType === 'wide') {
                    $bowlers[$bowlerId]['wides'] += $extraRuns;
                }
                if ($extraType === 'no_ball') {
                    $bowlers[$bowlerId]['no_balls'] += $extraRuns;
                }

                if ($isWicket && ! in_array($wicketType, ['run_out', 'retired_out', 'other'], true)) {
                    $bowlers[$bowlerId]['wickets'] += 1;
                }
            }
        }

        $batters = array_map(static function (array $row): array {
            $balls = max(1, (int) $row['balls']);
            $row['strike_rate'] = round(((int) $row['runs'] * 100) / $balls, 2);
            return $row;
        }, $batters);

        uasort($batters, static fn (array $a, array $b): int => (int) $b['runs'] <=> (int) $a['runs']);

        $bowlers = array_map(static function (array $row): array {
            $balls = (int) $row['balls'];
            $overs = intdiv($balls, 6).'.'.($balls % 6);
            $row['overs'] = $overs;
            $row['economy'] = $balls > 0 ? round(((int) $row['runs'] / $balls) * 6, 2) : 0.0;
            return $row;
        }, $bowlers);

        uasort($bowlers, static function (array $a, array $b): int {
            $w = (int) $b['wickets'] <=> (int) $a['wickets'];
            return $w !== 0 ? $w : ((int) $a['runs'] <=> (int) $b['runs']);
        });

        return [
            'batters' => array_values($batters),
            'bowlers' => array_values($bowlers),
        ];
    }

    public function saveCricket(): void
    {
        if ($this->rejectIfLocked()) {
            return;
        }

        if (! $this->isCricket) {
            return;
        }

        $this->validate([
            'currentInnings' => ['required', 'integer', 'in:1,2'],
            'innings.1.batting_team_id' => ['nullable', 'integer'],
            'innings.1.runs' => ['required', 'integer', 'min:0'],
            'innings.1.wickets' => ['required', 'integer', 'min:0', 'max:10'],
            'innings.1.overs' => ['required', 'integer', 'min:0'],
            'innings.1.balls' => ['required', 'integer', 'min:0', 'max:5'],
            'innings.1.extras' => ['required', 'integer', 'min:0'],
            'innings.2.batting_team_id' => ['nullable', 'integer'],
            'innings.2.runs' => ['required', 'integer', 'min:0'],
            'innings.2.wickets' => ['required', 'integer', 'min:0', 'max:10'],
            'innings.2.overs' => ['required', 'integer', 'min:0'],
            'innings.2.balls' => ['required', 'integer', 'min:0', 'max:5'],
            'innings.2.extras' => ['required', 'integer', 'min:0'],
            'resultText' => ['nullable', 'string', 'max:255'],
            'progressNote' => ['nullable', 'string', 'max:2000'],
            'striker' => ['nullable', 'string', 'max:120'],
            'nonStriker' => ['nullable', 'string', 'max:120'],
            'bowler' => ['nullable', 'string', 'max:120'],
            'targetRuns' => ['nullable', 'integer', 'min:0'],
            'strikerPlayerId' => ['nullable', 'integer', 'min:0'],
            'nonStrikerPlayerId' => ['nullable', 'integer', 'min:0'],
            'bowlerPlayerId' => ['nullable', 'integer', 'min:0'],
        ]);

        $this->striker = (string) ($this->playerNameById($this->strikerPlayerId) ?? '');
        $this->nonStriker = (string) ($this->playerNameById($this->nonStrikerPlayerId) ?? '');
        $this->bowler = (string) ($this->playerNameById($this->bowlerPlayerId) ?? '');

        $payload = [
            'type' => 'cricket',
            'innings' => $this->innings,
            'current_innings' => $this->currentInnings,
            'meta' => [
                'toss' => [
                    'winner_team_id' => $this->tossWinnerTeamId ?: null,
                    'decision' => $this->tossDecision ?: null,
                ],
                'lineup' => [
                    'batting_team_id' => $this->battingTeamId ?: null,
                    'bowling_team_id' => $this->bowlingTeamId ?: null,
                    'striker_player_id' => $this->strikerPlayerId ?: null,
                    'non_striker_player_id' => $this->nonStrikerPlayerId ?: null,
                    'bowler_player_id' => $this->bowlerPlayerId ?: null,
                ],
                'striker' => $this->striker,
                'non_striker' => $this->nonStriker,
                'bowler' => $this->bowler,
                'target_runs' => $this->targetRuns,
                'recent_events' => $this->recentEvents,
                'ball_history' => $this->ballHistory,
                'over_breakdown' => $this->overBreakdown,
                'partnerships' => $this->partnerships,
            ],
        ];

        $resultText = $this->resultText ?: null;
        $outcome = ['winner_team_id' => null, 'loser_team_id' => null];
        if ($this->fixture->status === 'completed') {
            $resultText = $this->generateAutoResult() ?: $resultText;
            $this->resultText = (string) ($resultText ?? '');
            $outcome = $this->resolveOutcomeTeamIds();
        }

        $this->fixture->update([
            'score_payload' => $payload,
            'current_innings' => $this->currentInnings,
            'winner_team_id' => $outcome['winner_team_id'],
            'loser_team_id' => $outcome['loser_team_id'],
            'result_text' => $resultText,
            'notes' => $this->progressNote ?: null,
            'home_points' => null,
            'away_points' => null,
        ]);

        $this->fixture->refresh();
        $this->dispatch('toast', message: 'Cricket scorecard updated.');
    }

    public function render()
    {
        $teams = Team::query()
            ->where('admin_id', (int) auth()->id())
            ->where('tournament_id', (int) $this->fixture->tournament_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        $homeId = (int) ($this->fixture->home_team_id ?? 0);
        $awayId = (int) ($this->fixture->away_team_id ?? 0);

        $homePlayers = collect();
        $awayPlayers = collect();

        if ($homeId > 0) {
            $homePlayers = Player::query()
                ->where('tournament_id', (int) $this->fixture->tournament_id)
                ->where('sold_team_id', $homeId)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        if ($awayId > 0) {
            $awayPlayers = Player::query()
                ->where('tournament_id', (int) $this->fixture->tournament_id)
                ->where('sold_team_id', $awayId)
                ->orderBy('name')
                ->get(['id', 'name']);
        }

        $playersByTeam = [
            $homeId => $homePlayers,
            $awayId => $awayPlayers,
        ];

        $scorecardTables = $this->isCricket
            ? $this->buildLiveScorecardTables($playersByTeam)
            : ['batters' => [], 'bowlers' => []];

        return view('livewire.admin.fixtures.scorer', [
            'teams' => $teams,
            'playersByTeam' => $playersByTeam,
            'battingScorecard' => $scorecardTables['batters'],
            'bowlingScorecard' => $scorecardTables['bowlers'],
        ]);
    }
}
