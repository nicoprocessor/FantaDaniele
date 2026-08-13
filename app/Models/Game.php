<?php

namespace App\Models;

use Database\Factories\GameFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $destination
 * @property Carbon $departure_at
 * @property string $status
 * @property int|null $arrival_minute
 * @property int|null $winner_user_id
 * @property string|null $winner_type
 */
class Game extends Model
{
    /** @use HasFactory<GameFactory> */
    use HasFactory;

    protected $fillable = ['created_by', 'title', 'destination', 'departure_at', 'status', 'arrival_minute', 'winner_user_id', 'winner_type', 'started_at', 'closed_at'];

    protected function casts(): array
    {
        return ['arrival_minute' => 'integer', 'departure_at' => 'datetime', 'started_at' => 'datetime', 'closed_at' => 'datetime'];
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_user_id');
    }

    /** @return HasMany<GameBet, $this> */
    public function bets(): HasMany
    {
        return $this->hasMany(GameBet::class);
    }

    /** @return HasMany<GameArrivalProposal, $this> */
    public function arrivalProposals(): HasMany
    {
        return $this->hasMany(GameArrivalProposal::class);
    }
}
