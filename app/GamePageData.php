<?php

namespace App;

use App\Models\Game;
use App\Models\GameArrivalProposal;
use App\Models\GameArrivalVote;
use App\Models\GameBet;
use App\Models\GamePropertyGrant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

final class GamePageData
{
    /** @return array<string, mixed> */
    public function dashboard(User $user): array
    {
        $game = $this->activeGame();

        return [
            'game' => $game === null ? null : $this->gameSummary($game),
            'metrics' => $this->metrics($user),
            'leaderboard' => $this->leaderboard($user)->take(5)->values()->all(),
            'canStartGame' => $user->canAdministerGames(),
        ];
    }

    /** @return array<string, mixed> */
    public function games(User $user): array
    {
        $current = $this->activeGame();
        $history = Game::query()->where('status', 'closed')->with(['creator:id,name,avatar_seed', 'bets.user:id,name,avatar_seed', 'winner:id,name'])->orderByDesc('closed_at')->orderByDesc('id')->get()->map(fn (Game $game): array => $this->game($game, $user))->all();

        return [
            'currentGame' => $current === null ? null : $this->gameSummary($current),
            'history' => $history,
            'canStartGame' => $user->canAdministerGames(),
        ];
    }

    /** @return array<string, mixed> */
    public function show(Game $game, User $user): array
    {
        $game->load([
            'creator:id,name,avatar_seed',
            'bets.user:id,name,avatar_seed',
            'arrivalProposals' => fn ($query) => $query->latest('id'),
            'arrivalProposals.proposer:id,name,avatar_seed',
            'arrivalProposals.votes.user:id,name,avatar_seed',
        ]);

        return [
            'game' => $this->game($game, $user),
            'myBet' => $this->myBet($game, $user),
            'availableBalance' => $user->balance,
            'canManageGame' => $this->canManageGame($game, $user),
            'proposals' => $game->arrivalProposals->map(fn (GameArrivalProposal $proposal): array => $this->arrivalProposal($proposal, $user))->values()->all(),
            'serverNow' => now(config('app.timezone'))->toIso8601String(),
            'closesAt' => $game->created_at->copy()->setTimezone(config('app.timezone'))->addDay()->startOfDay()->toIso8601String(),
        ];
    }

    /** @return array<string, mixed> */
    public function statistics(User $user): array
    {
        $games = Game::query()->where('status', 'closed')->with(['bets.user:id,name,avatar_seed'])->orderBy('closed_at')->orderBy('id')->get();

        if ($games->isEmpty()) {
            return [
                'arrivalTrend' => [],
                'propertyLabels' => [],
                'propertyTrend' => [],
            ];
        }

        return [
            'arrivalTrend' => $games->map(fn (Game $game): array => ['gameId' => $game->id, 'label' => $game->closed_at?->format('d/m') ?? (string) $game->id, 'actualMinute' => $game->arrival_minute, 'averageBetMinute' => $game->bets->isEmpty() ? null : (int) round($game->bets->avg('arrival_minute'))])->all(),
            'propertyLabels' => $games->map(fn (Game $game): string => $game->closed_at?->format('d/m') ?? (string) $game->id)->all(),
            'propertyTrend' => $this->propertyTrend($games, $user),
        ];
    }

    /** @return Collection<int, array{position: int, id: int, playerName: string, avatarUrl: string, balance: int, wins: int, gamesPlayed: int, winRate: float, isCurrentUser: bool}> */
    public function leaderboard(User $currentUser): Collection
    {
        return User::query()->withCount(['gameBets as games_played', 'gamesWon as wins'])->orderByDesc('balance')->orderByDesc('wins')->orderBy('name')->get(['id', 'name', 'balance', 'avatar_seed'])->values()->map(function (User $user, int $index) use ($currentUser): array {
            $gamesPlayed = (int) $user->games_played;
            $wins = (int) $user->wins;

            return ['position' => $index + 1, 'id' => $user->id, 'playerName' => $user->name, 'avatarUrl' => $this->avatarUrl($user), 'balance' => $user->balance, 'wins' => $wins, 'gamesPlayed' => $gamesPlayed, 'winRate' => $gamesPlayed === 0 ? 0.0 : round($wins / $gamesPlayed * 100, 1), 'isCurrentUser' => $user->is($currentUser)];
        });
    }

    private function activeGame(): ?Game
    {
        return Game::query()
            ->whereIn('status', ['open', 'started'])
            ->with(['creator:id,name,avatar_seed', 'bets.user:id,name,avatar_seed'])
            ->latest('id')
            ->first();
    }

    /** @return array<string, mixed> */
    private function game(Game $game, User $user): array
    {
        return [
            'id' => $game->id,
            'title' => $game->title,
            'destination' => $game->destination,
            'departureAt' => $game->departure_at->format('H:i'),
            'status' => match ($game->status) {
                'open' => 'open', 'started' => 'locked', default => 'completed'
            },
            'houseAmount' => (int) $game->bets->sum('amount'),
            'actualArrivalTime' => $game->arrival_minute === null ? null : $this->time($game->arrival_minute),
            'winnerName' => $game->winner_type === 'exact' ? $game->winner?->name : ($game->status === 'closed' ? 'Lo sport' : null),
            'myBet' => $this->myBet($game, $user),
            'owner' => $this->owner($game),
            'participants' => $game->bets->sortBy('created_at')->map(fn (GameBet $bet): array => ['id' => $bet->id, 'name' => $bet->user->name, 'avatarUrl' => $this->avatarUrl($bet->user), 'arrivalTime' => $this->time($bet->arrival_minute), 'stake' => $bet->amount, 'betAt' => $bet->created_at->toIso8601String(), 'isCurrentUser' => $bet->user->is($user)])->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function gameSummary(Game $game): array
    {
        return [
            'id' => $game->id,
            'title' => $game->title,
            'destination' => $game->destination,
            'departureAt' => $game->departure_at->format('H:i'),
            'status' => $game->status === 'open' ? 'open' : 'locked',
            'houseAmount' => (int) $game->bets->sum('amount'),
            'participantCount' => $game->bets->count(),
            'owner' => $this->owner($game),
        ];
    }

    /** @return array{arrivalTime: string, houseAmount: int, submittedAt: string}|null */
    private function myBet(Game $game, User $user): ?array
    {
        $bet = $game->bets->firstWhere('user_id', $user->id);

        return $bet === null ? null : ['arrivalTime' => $this->time($bet->arrival_minute), 'houseAmount' => $bet->amount, 'submittedAt' => $bet->created_at->toIso8601String()];
    }

    /** @return array{available: int, gamesPlayed: int, wins: int, draws: int, losses: int, winRate: float} */
    private function metrics(User $user): array
    {
        $gamesPlayed = GameBet::query()->whereBelongsTo($user)->count();
        $wins = Game::query()->where('winner_user_id', $user->id)->where('winner_type', 'exact')->count();
        $draws = Game::query()
            ->where('status', 'closed')
            ->where(function (Builder $query): void {
                $query->where('winner_type', 'sport')->orWhereNull('winner_type');
            })
            ->whereHas('bets', fn (Builder $query): Builder => $query->where('user_id', $user->id))
            ->count();
        $losses = Game::query()->where('winner_type', 'exact')->whereNot('winner_user_id', $user->id)->whereHas('bets', fn ($query) => $query->where('user_id', $user->id))->count();

        return ['available' => $user->balance, 'gamesPlayed' => $gamesPlayed, 'wins' => $wins, 'draws' => $draws, 'losses' => $losses, 'winRate' => $gamesPlayed === 0 ? 0 : round($wins / $gamesPlayed * 100, 1)];
    }

    /** @return array<string, mixed> */
    private function arrivalProposal(GameArrivalProposal $proposal, User $currentUser): array
    {
        $yesVotes = $proposal->votes->where('approved', true)->count();
        $voteCount = $proposal->votes->count();

        return [
            'id' => $proposal->id,
            'proposedTime' => $this->time($proposal->arrival_minute),
            'proposer' => ['id' => $proposal->proposer->id, 'name' => $proposal->proposer->name, 'avatarUrl' => $this->avatarUrl($proposal->proposer)],
            'votes' => $proposal->votes->sortBy('id')->map(fn (GameArrivalVote $vote): array => ['id' => $vote->id, 'voter' => ['id' => $vote->user->id, 'name' => $vote->user->name, 'avatarUrl' => $this->avatarUrl($vote->user)], 'choice' => $vote->approved ? 'yes' : 'no', 'isCurrentUser' => $vote->user->is($currentUser)])->values()->all(),
            'yesVotes' => $yesVotes,
            'noVotes' => $voteCount - $yesVotes,
            'hasMajority' => $voteCount > 0 && $yesVotes * 2 > $voteCount,
        ];
    }

    /** @return array{id: int, name: string, avatarUrl: string}|null */
    private function owner(Game $game): ?array
    {
        return $game->creator === null ? null : [
            'id' => $game->creator->id,
            'name' => $game->creator->name,
            'avatarUrl' => $this->avatarUrl($game->creator),
        ];
    }

    private function canManageGame(Game $game, User $user): bool
    {
        return $game->created_by === null
            ? $user->is_game_admin
            : $game->created_by === $user->id;
    }

    /**
     * @param  EloquentCollection<int, Game>  $games
     * @return list<array{id: int, name: string, avatarUrl: string, isCurrentUser: bool, values: list<int>}>
     */
    private function propertyTrend(EloquentCollection $games, User $currentUser): array
    {
        $players = User::query()->get(['id', 'name', 'balance', 'avatar_seed']);
        $bets = GameBet::query()->with('game:id,closed_at')->get();
        $grants = GamePropertyGrant::query()->get();

        return array_values($players->map(function (User $player) use ($bets, $games, $grants, $currentUser): array {
            $values = [];

            foreach ($games as $game) {
                $boundary = $game->closed_at;
                $futureBets = (int) $bets->where('user_id', $player->id)->filter(fn (GameBet $bet): bool => $bet->created_at->gt($boundary))->sum('amount');
                $futureGrants = (int) $grants->where('user_id', $player->id)->filter(fn (GamePropertyGrant $grant): bool => $grant->created_at->gt($boundary))->count();
                $futurePayouts = (int) $games->filter(fn (Game $payout): bool => $payout->winner_user_id === $player->id && $payout->closed_at->gt($boundary))->sum(fn (Game $payout): int => (int) $payout->bets->sum('amount'));
                $values[] = $player->balance + $futureBets - $futureGrants - $futurePayouts;
            }

            return ['id' => $player->id, 'name' => $player->name, 'avatarUrl' => $this->avatarUrl($player), 'isCurrentUser' => $player->is($currentUser), 'values' => $values];
        })->all());
    }

    private function avatarUrl(User $user): string
    {
        return route('avatars.show', $user).'?v='.rawurlencode($user->avatar_seed);
    }

    private function time(int $minute): string
    {
        return sprintf('%02d:%02d', intdiv($minute, 60), $minute % 60);
    }
}
