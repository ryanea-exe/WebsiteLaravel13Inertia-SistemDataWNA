<?php

namespace App\Policies;

use App\Models\Abg;
use App\Models\User;

class AbgPolicy
{
    /**
     * Determine whether the user can view any ABG records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific ABG record.
     */
    public function view(User $user, Abg $abg): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create ABG records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the ABG record.
     */
    public function update(User $user, Abg $abg): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the ABG record.
     */
    public function delete(User $user, Abg $abg): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the ABG list.
     */
    public function printList(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can export the ABG list to CSV.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
