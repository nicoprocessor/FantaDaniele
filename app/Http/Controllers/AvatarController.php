<?php

namespace App\Http\Controllers;

use App\AvatarSeeds;
use App\Models\User;
use App\OpenPeepsAvatar;
use Illuminate\Http\Response;

final class AvatarController extends Controller
{
    public function show(User $user, OpenPeepsAvatar $openPeepsAvatar): Response
    {
        return $this->response($openPeepsAvatar->render($user->avatar_seed));
    }

    public function preview(string $seed, OpenPeepsAvatar $openPeepsAvatar): Response
    {
        abort_unless(in_array($seed, AvatarSeeds::all(), true), 404);

        return $this->response($openPeepsAvatar->render($seed));
    }

    private function response(string $svg): Response
    {
        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=604800, immutable',
        ]);
    }
}
