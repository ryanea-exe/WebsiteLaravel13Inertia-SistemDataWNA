<?php

namespace App\Enums;

enum Gender: string
{
    case LakiLaki = 'L';
    case Perempuan = 'P';

    /**
     * Get human-readable Indonesian label.
     */
    public function label(): string
    {
        return match ($this) {
            self::LakiLaki => 'Laki-laki',
            self::Perempuan => 'Perempuan',
        };
    }
}
