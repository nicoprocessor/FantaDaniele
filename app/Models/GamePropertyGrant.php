<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePropertyGrant extends Model
{
    protected $fillable = ['user_id', 'granted_on'];

    protected function casts(): array
    {
        return ['granted_on' => 'date'];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
