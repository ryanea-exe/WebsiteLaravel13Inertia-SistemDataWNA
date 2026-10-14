<?php

namespace App\Policies;

use App\Models\Penerbitan;
use App\Models\User;

class PenerbitanPolicy
{
    /**
     * Determine whether the user can view any Penerbitan records.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the specific Penerbitan record.
     */
    public function view(User $user, Penerbitan $penerbitan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create Penerbitan records.
     */
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the Penerbitan record.
     */
    public function update(User $user, Penerbitan $penerbitan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the Penerbitan record.
     */
    public function delete(User $user, Penerbitan $penerbitan): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can print the Penerbitan list.
     */
    public function printList(User $user): bool
    {
        return $user->isAdmin();
    }
}
