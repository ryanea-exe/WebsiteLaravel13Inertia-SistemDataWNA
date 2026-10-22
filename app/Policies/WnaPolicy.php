<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wna;

class WnaPolicy
{
    /**
     * Determine whether the user can view any WNA records.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific WNA record.
     */
    public function view(User $user, Wna $wna): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create WNA records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the WNA record.
     */
    public function update(User $user, Wna $wna): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the WNA record.
     */
    public function delete(User $user, Wna $wna): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the entire WNA list as PDF.
     */
    public function printList(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can access early warning monitoring.
     */
    public function viewEarlyWarning(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can export WNA records to CSV.
     */
    public function export(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the individual WNA card.
     */
    public function printCard(User $user, Wna $wna): bool
    {
        return true;
    }
}
