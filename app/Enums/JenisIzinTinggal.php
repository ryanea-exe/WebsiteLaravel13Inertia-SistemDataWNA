<?php

namespace App\Enums;

enum JenisIzinTinggal: string
{
    case ITAS = 'ITAS';
    case ITAP = 'ITAP';
    case VOA = 'VOA';
    case ITK = 'ITK';

    /**
     * Get human-readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::ITAS => 'Izin Tinggal Terbatas (ITAS)',
            self::ITAP => 'Izin Tinggal Tetap (ITAP)',
            self::VOA => 'Visa on Arrival (VOA)',
            self::ITK => 'Izin Tinggal Kunjungan (ITK)',
        };
    }
}
