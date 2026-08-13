<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameBet;
use App\Models\TeamInvitation;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $email = strtolower($request->user()->email);

        $pendingInvitations = TeamInvitation::query()
            ->with(['inviter', 'team'])
            ->whereRaw('LOWER(email) = ?', [$email])
            ->whereNull('accepted_at')
            ->where(fn ($query) => $query
                ->whereNull('expires_at')
                ->orWhere('expires_at', '>=', now()))
            ->latest()
            ->get()
            ->map(fn (TeamInvitation $invitation) => [
                'code' => $invitation->code,
                'inviterName' => $invitation->inviter->name,
                'team' => [
                    'name' => $invitation->team->name,
                    'slug' => $invitation->team->slug,
                ],
            ]);

        $game = Game::query()
            ->whereIn('status', ['open', 'started'])
            ->with([
                'bets.user:id,name',
                'arrivalProposals.proposer:id,name',
                'arrivalProposals.votes.user:id,name',
            ])
            ->latest('id')
            ->first();

        $user = $request->user();
        $myBet = $game?->bets->firstWhere('user_id', $user->id);
        $arrivalProposal = $game?->arrivalProposals->sortByDesc('id')->first();
        $totalPlayed = (int) GameBet::query()->whereBelongsTo($user)->sum('amount');
        $totalWon = (int) Game::query()->where('winner_user_id', $user->id)->withSum('bets', 'amount')->get()->sum('bets_sum_amount');
        $leaderboard = GameBet::query()
            ->selectRaw('user_id, SUM(amount) as total_amount')
            ->with('user:id,name')
            ->groupBy('user_id')
            ->orderByDesc('total_amount')
            ->get()
            ->values()
            ->map(fn (GameBet $bet, int $index) => [
                'position' => $index + 1,
                'playerName' => $bet->user->name,
                'points' => (int) $bet->total_amount,
                'balance' => $bet->user->balance,
                'history' => [(int) $bet->total_amount],
                'isCurrentUser' => $bet->user_id === $user->id,
            ]);

        return Inertia::render('dashboard', [
            'pendingInvitations' => $pendingInvitations,
            'balance' => ['available' => $user->balance, 'totalWon' => $totalWon, 'totalPlayed' => $totalPlayed],
            'isAdmin' => $user->canAdministerGames(),
            'myBet' => $myBet === null ? null : ['arrivalTime' => $this->timeFromMinute($myBet->arrival_minute), 'houseAmount' => $myBet->amount, 'submittedAt' => $myBet->created_at->toIso8601String()],
            'game' => $game === null ? null : [
                'id' => $game->id,
                'title' => $game->title,
                'destination' => $game->destination,
                'departureAt' => $game->departure_at->format('H:i'),
                'status' => $game->status === 'open' ? 'open' : 'locked',
                'houseAmount' => (int) $game->bets->sum('amount'),
            ],
            'arrivalProposal' => $arrivalProposal === null ? null : $this->arrivalProposalData($arrivalProposal),
            'votes' => $arrivalProposal?->votes->map(fn ($vote) => ['id' => $vote->id, 'voterName' => $vote->user->name, 'choice' => $vote->approved ? 'yes' : 'no'])->values() ?? [],
            'leaderboard' => $leaderboard,
            'slots' => [],
            'history' => [],
        ]);
    }

    /**
     * Format stored minute-of-day for dashboard.
     */
    private function timeFromMinute(int $minute): string
    {
        return sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
    }

    /**
     * Map one proposal to dashboard contract.
     *
     * @return array{id: int, proposedTime: string, proposerName: string, closesAt: string, status: string}
     */
    private function arrivalProposalData(GameArrivalProposal $proposal): array
    {
        $approved = $proposal->votes->isNotEmpty()
            && $proposal->votes->where('approved', true)->count() * 2 > $proposal->votes->count();

        return [
            'id' => $proposal->id,
            'proposedTime' => $this->timeFromMinute($proposal->arrival_minute),
            'proposerName' => $proposal->proposer->name,
            'closesAt' => $proposal->created_at->addHour()->format('H:i'),
            'status' => $approved ? 'confirmed' : 'open',
        ];
    }
}
