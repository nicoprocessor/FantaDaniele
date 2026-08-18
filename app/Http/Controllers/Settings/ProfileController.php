<?php

namespace App\Http\Controllers\Settings;

use App\AvatarSeeds;
use App\Enums\TeamRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileDeleteRequest;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'avatarSeeds' => AvatarSeeds::all(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Profile updated.')]);

        return to_route('profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(ProfileDeleteRequest $request): RedirectResponse
    {
        $user = $request->user();

        DB::transaction(function () use ($user): void {
            $deletingUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $team = Team::query()->where('slug', 'gruppo-daniele')->lockForUpdate()->first();

            if ($team !== null) {
                $membership = $team->memberships()
                    ->where('user_id', $deletingUser->id)
                    ->lockForUpdate()
                    ->first();

                $hasRemainingOwner = $team->memberships()
                    ->where('role', TeamRole::Owner->value)
                    ->where('user_id', '!=', $deletingUser->id)
                    ->exists();

                if ($membership?->role === TeamRole::Owner && ! $hasRemainingOwner) {
                    $team->memberships()
                        ->where('user_id', '!=', $deletingUser->id)
                        ->orderBy('user_id')
                        ->lockForUpdate()
                        ->first()
                        ?->update(['role' => TeamRole::Owner]);
                }
            }

            $deletingUser->delete();
        });

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
