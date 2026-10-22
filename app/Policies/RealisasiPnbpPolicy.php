<?php

namespace App\Policies;

use App\Models\RealisasiPnbp;
use App\Models\User;

class RealisasiPnbpPolicy
{
    /**
     * Determine whether the user can view any Realisasi PNBP records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific Realisasi PNBP record.
     */
    public function view(User $user, RealisasiPnbp $realisasiPnbp): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create Realisasi PNBP records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the Realisasi PNBP record.
     */
    public function update(User $user, RealisasiPnbp $realisasiPnbp): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the Realisasi PNBP record.
     */
    public function delete(User $user, RealisasiPnbp $realisasiPnbp): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the Realisasi PNBP list.
     */
    public function printList(User $user): bool
    {
        return $user->isAdmin();
    }
}
