<?php

namespace App\Enums;

enum JenisIzinTinggal: string
{
    case ITAS = 'ITAS';
    case ITAP = 'ITAP';
    case VOA = 'VOA';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ITAS => 'Izin Tinggal Terbatas (ITAS)',
            self::ITAP => 'Izin Tinggal Tetap (ITAP)',
            self::VOA => 'Visa on Arrival (VOA)',
        };
    }
}
