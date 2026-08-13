<?php

namespace App\Models;

use Database\Factories\GameArrivalVoteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameArrivalVote extends Model
{
    /** @use HasFactory<GameArrivalVoteFactory> */
    use HasFactory;

    protected $fillable = ['game_arrival_proposal_id', 'user_id', 'approved'];

    protected function casts(): array
    {
        return ['approved' => 'boolean'];
    }

    /** @return BelongsTo<GameArrivalProposal, $this> */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(GameArrivalProposal::class, 'game_arrival_proposal_id');
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
