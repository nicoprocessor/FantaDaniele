<?php

namespace App\Models;

use Database\Factories\GameBetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $game_id
 * @property int $user_id
 * @property int $amount
 * @property int $arrival_minute
 * @property int $total_amount
 */
class GameBet extends Model
{
    /** @use HasFactory<GameBetFactory> */
    use HasFactory;

    protected $fillable = ['game_id', 'user_id', 'amount', 'arrival_minute'];

    protected function casts(): array
    {
        return ['amount' => 'integer', 'arrival_minute' => 'integer'];
    }

    /** @return BelongsTo<Game, $this> */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
