<?php

namespace App\Livewire\Admin\Players;

use App\Models\Player;
use App\Models\PlayerCategory;
use App\Models\Tournament;
use App\Models\Team;
use App\Support\AdminQuota;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Manager extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Auction edit modal state
    public ?int $editAuctionPlayerId = null;
    public ?int $editAuctionTeamId = null;
    public $editAuctionAmount = null;
    public array $editAuctionTeams = [];
    public $editAuctionStepUp = 0;
    public $editAuctionError = '';

    public function openEditAuctionModal(int $playerId): void
    {
        $player = Player::query()->findOrFail($playerId);
        if ($player->status !== 'sold') {
            $this->dispatch('toast', message: 'Only sold players can have auction details edited.');
            return;
        }
        $tournament = Tournament::findOrFail($player->tournament_id);
        $this->editAuctionPlayerId = $player->id;
        $this->editAuctionTeamId = $player->sold_team_id;
        $this->editAuctionAmount = $player->final_price;
        $this->editAuctionTeams = Team::where('tournament_id', $player->tournament_id)->get(['id','name','wallet_balance'])->toArray();
        $this->editAuctionStepUp = (float) ($tournament->base_increment ?? 1);
        $this->editAuctionError = '';
        $this->dispatch('show-edit-auction-modal');
    }

    public function saveEditAuctionDetails(): void
    {
        $player = Player::query()->findOrFail($this->editAuctionPlayerId);
        $tournament = Tournament::findOrFail($player->tournament_id);
        $stepUp = (float) ($tournament->base_increment ?? 1);
        $amount = (float) $this->editAuctionAmount;
        $newTeamId = (int) $this->editAuctionTeamId;
        $oldTeamId = (int) $player->sold_team_id;
        $oldAmount = (float) $player->final_price;
        if ($amount < $stepUp || fmod($amount, $stepUp) !== 0.0) {
            $this->editAuctionError = 'Amount must be a multiple of step up ('.$stepUp.') and at least step up.';
            return;
        }
        $newTeam = Team::findOrFail($newTeamId);
        if ($newTeam->wallet_balance + ($oldTeamId === $newTeamId ? $oldAmount : 0) < $amount) {
            $this->editAuctionError = 'Selected team does not have enough wallet balance.';
            return;
        }
        \DB::beginTransaction();
        try {
            // Refund old team if changed
            if ($oldTeamId && $oldTeamId !== $newTeamId) {
                $oldTeam = Team::find($oldTeamId);
                if ($oldTeam) {
                    $oldTeam->wallet_balance += $oldAmount;
                    $oldTeam->save();
                }
            }
            // Debit new team
            if ($newTeamId) {
                if ($oldTeamId === $newTeamId) {
                    $newTeam->wallet_balance += $oldAmount; // refund old first
                }
                $newTeam->wallet_balance -= $amount;
                $newTeam->save();
            }
            $player->sold_team_id = $newTeamId;
            $player->final_price = $amount;
            $player->save();
            \DB::commit();
            $this->editAuctionPlayerId = null;
            $this->editAuctionTeamId = null;
            $this->editAuctionAmount = null;
            $this->editAuctionTeams = [];
            $this->editAuctionStepUp = 0;
            $this->editAuctionError = '';
            $this->dispatch('toast', message: 'Auction details updated.');
            $this->dispatch('hide-edit-auction-modal');
        } catch (\Throwable $e) {
            \DB::rollBack();
            $this->editAuctionError = 'Failed to update auction details.';
        }
    }

    public int $tournamentId = 0;
    public ?int $editingId = null;
    public bool $formMode = false;
    public int $formTournamentId = 0;
    public ?int $categoryId = null;
    public ?int $serialNo = null;
    public string $name = '';
    public $basePrice = 0;
    public ?int $age = null;
    public string $country = '';
    public string $previousTeam = '';
    public string $status = 'available';
    public $image;
    public ?string $existingImagePath = null;
    public $importFile;
    public array $importSummary = [];

    public function updatedTournamentId(): void
    {
        $this->resetPage();
    }

    public function updatedFormTournamentId(): void
    {
        $this->categoryId = null;
    }

    public function isFormPage(): bool
    {
        return $this->formMode;
    }

    public function save(): void
    {
        $adminId = (int) auth()->id();

        $validated = $this->validate([
            'formTournamentId' => [
                'required',
                'integer',
                Rule::exists('tournaments', 'id')->where(fn ($query) => $query->where('admin_id', auth()->id())),
            ],
            'categoryId' => [
                'nullable',
                'integer',
                Rule::exists('player_categories', 'id')->where(fn ($query) => $query->where('tournament_id', $this->formTournamentId)),
            ],
            'name' => ['required', 'string', 'max:255'],
            'serialNo' => [
                'required',
                'integer',
                'min:1',
                Rule::unique('players', 'serial_no')
                    ->where(fn ($query) => $query->where('tournament_id', $this->formTournamentId))
                    ->ignore($this->editingId),
            ],
            'basePrice' => ['required', 'numeric', 'min:0'],
            'age' => ['nullable', 'integer', 'between:10,60'],
            'country' => ['nullable', 'string', 'max:100'],
            'previousTeam' => ['nullable', 'string', 'max:255'],
            'status' => ['required', 'in:available,sold,unsold,retained,withdrawn'],
            'image' => ['nullable', 'image', 'max:2048'],
        ], [
            'formTournamentId.required' => 'Please select a tournament.',
            'formTournamentId.integer' => 'Please select a valid tournament.',
            'name.required' => 'Player name is required.',
            'basePrice.required' => 'Base price is required.',
            'basePrice.numeric' => 'Base price must be a valid number.',
        ]);

        if (! $this->editingId) {
            $limitMessage = AdminQuota::playerLimitMessage($adminId);
            if ($limitMessage) {
                $this->addError('name', $limitMessage);
                $this->dispatch('toast', message: $limitMessage);

                return;
            }
        }

        try {
            $payload = [
                'admin_id' => $adminId,
                'tournament_id' => (int) $validated['formTournamentId'],
                'category_id' => $validated['categoryId'] ? (int) $validated['categoryId'] : null,
                'name' => trim((string) $validated['name']),
                'serial_no' => (int) $validated['serialNo'],
                'base_price' => (float) $validated['basePrice'],
                'age' => $validated['age'] ? (int) $validated['age'] : null,
                'country' => $this->country !== '' ? trim($this->country) : null,
                'previous_team' => $this->previousTeam !== '' ? trim($this->previousTeam) : null,
                'status' => $validated['status'],
            ];

            if ($this->editingId) {
                $player = Player::query()
                    ->where('admin_id', auth()->id())
                    ->findOrFail($this->editingId);

                if ($this->image) {
                    if ($player->image_path) {
                        Storage::disk('public')->delete($player->image_path);
                    }
                    $payload['image_path'] = $this->image->store('players', 'public');
                }

                $player->update($payload);
                $message = 'Player updated.';
            } else {
                if ($this->image) {
                    $payload['image_path'] = $this->image->store('players', 'public');
                }
                Player::query()->create($payload);
                $message = 'Player created.';
            }

            $this->resetForm();
            $this->dispatch('toast', message: $message);
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('name', 'Unable to save player right now. Please try again.');
            $this->dispatch('toast', message: 'Unable to save player. Check inputs and try again.');
        }
    }

    public function edit(int $playerId): void
    {
        $player = Player::query()->where('admin_id', auth()->id())->findOrFail($playerId);
        $this->editingId = $player->id;
        $this->formTournamentId = (int) $player->tournament_id;
        $this->categoryId = $player->category_id ? (int) $player->category_id : null;
        $this->name = $player->name;
        $this->serialNo = $player->serial_no ? (int) $player->serial_no : null;
        $this->basePrice = (float) $player->base_price;
        $this->age = $player->age ? (int) $player->age : null;
        $this->country = (string) ($player->country ?? '');
        $this->previousTeam = (string) ($player->previous_team ?? '');
        $this->status = $player->status;
        $this->existingImagePath = $player->image_path;
        $this->image = null;
    }

    public function delete(int $playerId): void
    {
        $player = Player::query()->where('admin_id', auth()->id())->findOrFail($playerId);

        if ($player->status === 'sold') {
            $this->dispatch('toast', message: 'Sold players cannot be deleted.');
            return;
        }

        if ($player->image_path) {
            Storage::disk('public')->delete($player->image_path);
        }
        $player->delete();
        if ($this->editingId === $playerId) {
            $this->resetForm();
        }
        $this->dispatch('toast', message: 'Player deleted.');
    }

    public function resetForm(): void
    {
        $this->reset([
            'editingId',
            'formTournamentId',
            'categoryId',
            'name',
            'serialNo',
            'basePrice',
            'age',
            'country',
            'previousTeam',
            'status',
            'image',
            'existingImagePath',
        ]);
        $this->basePrice = 0;
        $this->status = 'available';
    }

    public function mount(?int $player = null): void
    {
        $this->resetForm();
        $this->formMode = in_array((string) request()->route()?->getName(), ['admin.players.create', 'admin.players.edit'], true);

        if ($player) {
            $this->edit($player);
        }
    }

    public function importPlayers(): void
    {
        $adminId = (int) auth()->id();

        if ($this->tournamentId <= 0) {
            $this->addError('importFile', 'Select a tournament in filter before importing.');
            $this->dispatch('toast', message: 'Select a tournament first.');
            return;
        }

        $validated = $this->validate([
            'importFile' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ], [
            'importFile.required' => 'Please choose a CSV file to import.',
            'importFile.mimes' => 'Only CSV files are allowed.',
            'importFile.max' => 'CSV file must be 5MB or less.',
        ]);

        $quota = AdminQuota::playerStats($adminId);
        if (($quota['remaining'] ?? 0) <= 0) {
            $message = AdminQuota::playerLimitMessage($adminId) ?? 'Player limit reached for your plan.';
            $this->addError('importFile', $message);
            $this->dispatch('toast', message: $message);
            return;
        }

        /** @var UploadedFile $file */
        $file = $validated['importFile'];
        $handle = fopen($file->getRealPath(), 'r');

        if (! $handle) {
            $this->addError('importFile', 'Unable to read CSV file.');
            $this->dispatch('toast', message: 'Unable to read CSV file.');
            return;
        }

        $header = fgetcsv($handle);
        if (! is_array($header) || count($header) === 0) {
            fclose($handle);
            $this->addError('importFile', 'CSV file is empty or invalid.');
            $this->dispatch('toast', message: 'CSV file is empty or invalid.');
            return;
        }

        $headers = collect($header)
            ->map(function ($col) {
                $normalized = trim((string) $col);
                // Remove UTF-8 BOM from first column/header if present.
                $normalized = preg_replace('/^\xEF\xBB\xBF/', '', $normalized) ?? $normalized;

                return strtolower($normalized);
            })
            ->values()
            ->all();

        $requiredHeaders = ['name', 'serial_no', 'base_price'];
        foreach ($requiredHeaders as $requiredHeader) {
            if (! in_array($requiredHeader, $headers, true)) {
                fclose($handle);
                $this->addError('importFile', "CSV is missing required column: {$requiredHeader}");
                $this->dispatch('toast', message: "CSV missing column: {$requiredHeader}");
                return;
            }
        }

        $remaining = (int) ($quota['remaining'] ?? 0);
        $existingSerials = Player::query()
            ->where('admin_id', $adminId)
            ->where('tournament_id', $this->tournamentId)
            ->pluck('serial_no')
            ->filter()
            ->map(fn ($value) => (int) $value)
            ->flip();

        $categoryMap = PlayerCategory::query()
            ->where('tournament_id', $this->tournamentId)
            ->get(['id', 'name'])
            ->mapWithKeys(fn (PlayerCategory $category) => [strtolower(trim($category->name)) => (int) $category->id]);

        $created = 0;
        $skipped = 0;
        $line = 1;
        $errors = [];

        while (($row = fgetcsv($handle)) !== false) {
            $line++;

            if ($row === [null] || count(array_filter($row, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }

            $assoc = [];
            foreach ($headers as $index => $column) {
                $assoc[$column] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }

            $name = trim((string) ($assoc['name'] ?? ''));
            $serialNoRaw = (string) ($assoc['serial_no'] ?? '');
            $basePriceRaw = (string) ($assoc['base_price'] ?? '');
            $status = strtolower(trim((string) ($assoc['status'] ?? 'available')));
            $status = $status === '' ? 'available' : $status;
            $categoryName = strtolower(trim((string) ($assoc['category'] ?? '')));
            $ageRaw = trim((string) ($assoc['age'] ?? ''));
            $country = trim((string) ($assoc['country'] ?? ''));
            $previousTeam = trim((string) ($assoc['previous_team'] ?? ''));

            if ($created >= $remaining) {
                $skipped++;
                $errors[] = "Line {$line}: quota reached, remaining rows skipped.";
                break;
            }

            if ($name === '') {
                $skipped++;
                $errors[] = "Line {$line}: name is required.";
                continue;
            }

            if (! is_numeric($serialNoRaw) || (int) $serialNoRaw < 1) {
                $skipped++;
                $errors[] = "Line {$line}: serial_no must be a positive integer.";
                continue;
            }
            $serialNo = (int) $serialNoRaw;

            if (isset($existingSerials[$serialNo])) {
                $skipped++;
                $errors[] = "Line {$line}: serial_no {$serialNo} already exists in this tournament.";
                continue;
            }

            if (! is_numeric($basePriceRaw) || (float) $basePriceRaw < 0) {
                $skipped++;
                $errors[] = "Line {$line}: base_price must be a number >= 0.";
                continue;
            }

            if (! in_array($status, ['available', 'sold', 'unsold', 'retained', 'withdrawn'], true)) {
                $skipped++;
                $errors[] = "Line {$line}: invalid status '{$status}'.";
                continue;
            }

            $categoryId = null;
            if ($categoryName !== '') {
                $categoryId = $categoryMap[$categoryName] ?? null;
                if (! $categoryId) {
                    $skipped++;
                    $errors[] = "Line {$line}: category '{$categoryName}' not found for selected tournament.";
                    continue;
                }
            }

            $age = null;
            if ($ageRaw !== '') {
                if (! is_numeric($ageRaw) || (int) $ageRaw < 10 || (int) $ageRaw > 60) {
                    $skipped++;
                    $errors[] = "Line {$line}: age must be between 10 and 60.";
                    continue;
                }
                $age = (int) $ageRaw;
            }

            Player::query()->create([
                'admin_id' => $adminId,
                'tournament_id' => (int) $this->tournamentId,
                'category_id' => $categoryId,
                'name' => $name,
                'serial_no' => $serialNo,
                'base_price' => (float) $basePriceRaw,
                'age' => $age,
                'country' => $country !== '' ? $country : null,
                'previous_team' => $previousTeam !== '' ? $previousTeam : null,
                'status' => $status,
            ]);

            $existingSerials[$serialNo] = true;
            $created++;
        }

        fclose($handle);

        $this->importSummary = [
            'created' => $created,
            'skipped' => $skipped,
            'errors' => array_slice($errors, 0, 12),
        ];

        $this->importFile = null;
        $this->dispatch('toast', message: "Import complete. Created {$created}, skipped {$skipped}.");
        $this->resetPage();
    }

    public function downloadImportTemplate()
    {
        $headers = ['name', 'serial_no', 'base_price', 'category', 'status', 'age', 'country', 'previous_team'];
        $rows = [
            ['Virat Kohli', '18', '2000', 'Batsman', 'available', '35', 'India', 'RCB'],
            ['Jasprit Bumrah', '93', '1800', 'Bowler', 'available', '30', 'India', 'MI'],
        ];

        return response()->streamDownload(function () use ($headers, $rows): void {
            $handle = fopen('php://output', 'w');
            if (! $handle) {
                return;
            }

            // UTF-8 BOM helps spreadsheet apps detect encoding correctly.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, $headers);

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'players-import-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function render()
    {
        $adminId = (int) auth()->id();
        $tournamentIds = Tournament::where('admin_id', $adminId)->pluck('id');
        $isFormPage = $this->isFormPage();

        return view($isFormPage ? 'livewire.admin.players.form' : 'livewire.admin.players.index', [
            'tournaments' => Tournament::where('admin_id', $adminId)->get(['id', 'name']),
            'quota' => AdminQuota::playerStats($adminId),
            'categories' => $this->formTournamentId > 0
                ? PlayerCategory::query()->where('tournament_id', $this->formTournamentId)->orderBy('name')->get(['id', 'name'])
                : collect(),
            'players' => Player::query()
                ->when($this->tournamentId > 0, fn ($query) => $query->where('tournament_id', $this->tournamentId))
                ->where('admin_id', $adminId)
                ->whereIn('tournament_id', $tournamentIds)
                ->with(['tournament:id,name', 'category:id,name'])
                ->latest()
                ->paginate(20),
        ]);
    }
}
