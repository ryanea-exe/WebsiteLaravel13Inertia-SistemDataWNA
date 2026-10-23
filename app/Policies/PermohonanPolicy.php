<?php

namespace App\Policies;

use App\Models\Permohonan;
use App\Models\User;

class PermohonanPolicy
{
    /**
     * Determine whether the user can view any Permohonan records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific Permohonan record.
     */
    public function view(User $user, Permohonan $permohonan): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create Permohonan records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the Permohonan record.
     */
    public function update(User $user, Permohonan $permohonan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the Permohonan record.
     */
    public function delete(User $user, Permohonan $permohonan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the Permohonan list.
     */
    public function printList(User $user): bool
    {
        return true;
    }
}
