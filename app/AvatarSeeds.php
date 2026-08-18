<?php

namespace App;

final class AvatarSeeds
{
    /** @return list<string> */
    public static function all(): array
    {
        return ['Ariete', 'Caffè', 'Ciclamino', 'Corallo', 'Focaccia', 'Gelsomino', 'Lenticchia', 'Mandarino', 'Mirtillo', 'Pistacchio', 'Sole', 'Tiramisu'];
    }

    public static function default(): string
    {
        return self::all()[0];
    }

    public static function forIdentity(string $identity): string
    {
        return self::all()[(int) (sprintf('%u', crc32($identity)) % count(self::all()))];
    }
}
