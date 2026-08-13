<?php

namespace App\Models;

use Database\Factories\GameArrivalProposalFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GameArrivalProposal extends Model
{
    /** @use HasFactory<GameArrivalProposalFactory> */
    use HasFactory;

    protected $fillable = ['game_id', 'proposed_by', 'arrival_minute'];

    protected function casts(): array
    {
        return ['arrival_minute' => 'integer'];
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /** @return BelongsTo<User, $this> */
    public function proposer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'proposed_by');
    }

    /** @return HasMany<GameArrivalVote, $this> */
    public function votes(): HasMany
    {
        return $this->hasMany(GameArrivalVote::class);
    }
}
