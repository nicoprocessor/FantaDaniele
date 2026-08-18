<?php

namespace App\Actions\Fortify;

use App\AvatarSeeds;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\JoinDefaultTeam;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    public function __construct(private JoinDefaultTeam $joinDefaultTeam) {}

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => $this->nameRules(),
            'email' => $this->emailRules(null),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'avatar_seed' => AvatarSeeds::forIdentity($input['email']),
            ]);

            $this->joinDefaultTeam->handle($user);

            return $user;
        });
    }
}
