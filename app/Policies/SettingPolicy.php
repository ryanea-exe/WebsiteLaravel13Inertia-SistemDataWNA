<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy
{
    /**
     * Determine whether the user can view settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the setting.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update settings.
     */
    public function update(User $user, ?Setting $setting = null): bool
    {
        return $user->isAdmin();
    }
}
