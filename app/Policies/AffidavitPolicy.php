<?php

namespace App\Policies;

use App\Models\Affidavit;
use App\Models\User;

class AffidavitPolicy
{
    /**
     * Determine whether the user can view any Affidavit records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific Affidavit record.
     */
    public function view(User $user, Affidavit $affidavit): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create Affidavit records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the Affidavit record.
     */
    public function update(User $user, Affidavit $affidavit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the Affidavit record.
     */
    public function delete(User $user, Affidavit $affidavit): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the Affidavit list.
     */
    public function printList(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can export the Affidavit list to CSV.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }
}
