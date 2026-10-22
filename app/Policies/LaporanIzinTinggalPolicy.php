<?php

namespace App\Policies;

use App\Models\LaporanIzinTinggal;
use App\Models\User;

class LaporanIzinTinggalPolicy
{
    /**
     * Determine whether the user can view any Laporan Izin Tinggal records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific report record.
     */
    public function view(User $user, LaporanIzinTinggal $laporan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create report records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the report record.
     */
    public function update(User $user, LaporanIzinTinggal $laporan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the report record.
     */
    public function delete(User $user, LaporanIzinTinggal $laporan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the report list.
     */
    public function printList(User $user): bool
    {
        return $user->isAdmin();
    }
}
