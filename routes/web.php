<?php

use App\Http\Controllers\BidController;
use App\Http\Controllers\ExportDownloadController;
use App\Http\Controllers\Webhooks\RazorpayWebhookController;
use App\Http\Controllers\Webhooks\StripeWebhookController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\Register;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Admin\Auction\ControlPanel;
use App\Livewire\Admin\Categories\Manager as CategoriesManager;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Players\Manager as PlayersManager;
use App\Livewire\Admin\Reports as AdminReports;
use App\Livewire\Admin\Teams\Manager as TeamsManager;
use App\Livewire\Admin\Tournament\Create as TournamentCreate;
use App\Livewire\Admin\Tournament\Index as TournamentIndex;
use App\Livewire\Admin\Tournament\Settings as TournamentSettings;
use App\Livewire\Public\AuctionViewer;
use App\Livewire\SuperAdmin\AdminManager;
use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Livewire\SuperAdmin\PlatformSettings;
use App\Livewire\SuperAdmin\Reports as SuperAdminReports;
use App\Livewire\SuperAdmin\SportsManager;
use App\Livewire\SuperAdmin\SubscriptionManager;
use App\Livewire\Team\AuctionRoom;
use App\Livewire\Team\BidHistory;
use App\Livewire\Team\Dashboard as TeamDashboard;
use App\Livewire\Team\SquadView;
use App\Models\Auction;
use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $tournaments = Tournament::query()
        ->with([
            'sport:id,name',
            'auction:id,tournament_id,current_player_id,is_paused,ends_at',
        ])
        ->orderByDesc('starts_at')
        ->orderByDesc('id')
        ->limit(12)
        ->get([
            'id',
            'sport_id',
            'name',
            'status',
            'starts_at',
        ]);

    $runningTournamentIds = Auction::query()
        ->whereNotNull('current_player_id')
        ->where('is_paused', false)
        ->pluck('tournament_id')
        ->all();

    return view('welcome', [
        'tournaments' => $tournaments,
        'runningTournamentIds' => $runningTournamentIds,
    ]);
});

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/register', Register::class)->name('register');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});


Route::get('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');

Route::middleware(['auth', 'tenant'])->group(function () {
    Route::middleware(['role:SuperAdmin'])->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', SuperAdminDashboard::class)->name('dashboard');
        Route::get('/admins', AdminManager::class)->name('admins');
        Route::get('/sports', SportsManager::class)->name('sports');
        Route::get('/subscriptions', SubscriptionManager::class)->name('subscriptions');
        Route::get('/settings', PlatformSettings::class)->name('settings');
        Route::get('/reports', SuperAdminReports::class)->name('reports');
    });

    Route::middleware(['role:Admin', 'subscription.active'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
        Route::get('/tournaments', TournamentIndex::class)->name('tournaments.index');
        Route::get('/tournaments/create', TournamentCreate::class)->name('tournaments.create');
        Route::get('/tournaments/{tournament}/edit', TournamentSettings::class)->name('tournaments.edit');
        Route::get('/tournaments/{tournament}/settings', TournamentSettings::class)->name('tournaments.settings');
        Route::get('/teams', TeamsManager::class)->name('teams.index');
        Route::get('/teams/create', TeamsManager::class)->name('teams.create');
        Route::get('/teams/{team}/edit', TeamsManager::class)->name('teams.edit');
        Route::get('/players', PlayersManager::class)->name('players.index');
        Route::get('/players/create', PlayersManager::class)->name('players.create');
        Route::get('/players/{player}/edit', PlayersManager::class)->name('players.edit');
        Route::get('/categories', CategoriesManager::class)->name('categories');
        Route::get('/auction/{tournament}', ControlPanel::class)->name('auction.control');
        Route::get('/reports', AdminReports::class)->name('reports');
    });

    Route::middleware(['role:Team', 'subscription.active'])->prefix('team')->name('team.')->group(function () {
        Route::get('/dashboard', TeamDashboard::class)->name('dashboard');
        Route::get('/auction/{tournamentId}', AuctionRoom::class)->name('auction-room');
        Route::get('/squad/{tournamentId}', SquadView::class)->name('squad');
        Route::get('/bids/{tournamentId}', BidHistory::class)->name('bid-history');
    });

    Route::post('/bids/place', BidController::class)
        ->middleware('throttle:bids')
        ->name('bids.place');

    Route::get('/admin/exports/download', ExportDownloadController::class)
        ->middleware(['role:Admin', 'subscription.active'])
        ->name('admin.exports.download');
});

Route::get('/live/{tournamentId}', AuctionViewer::class)->name('public.auction-viewer');
Route::post('/webhooks/stripe', StripeWebhookController::class)->name('webhooks.stripe');
Route::post('/webhooks/razorpay', RazorpayWebhookController::class)->name('webhooks.razorpay');
