<?php

namespace App;

use DiceBear\Avatar;
use DiceBear\Style;
use RuntimeException;

final class OpenPeepsAvatar
{
    public function render(string $seed): string
    {
        $styleFile = base_path('vendor/dicebear/styles/src/open-peeps.json');
        $json = file_get_contents($styleFile);

        if ($json === false) {
            throw new RuntimeException("Unable to read the Open Peeps DiceBear style at {$styleFile}.");
        }

        return (string) new Avatar(Style::fromJson($json), ['seed' => $seed]);
    }
}
