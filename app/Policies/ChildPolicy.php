<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->roles !== null;
    }

    public function view(User $user, Child $child): bool
    {
        return Child::query()->visibleTo($user)->whereKey($child->getKey())->exists();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff() || $user->isParent();
    }

    /** Orang tua dan kader boleh mengubah data dan mencatat pengukuran; nakes hanya meninjau. */
    public function update(User $user, Child $child): bool
    {
        return $user->roles !== UserRole::Nakes && $this->view($user, $child);
    }

    public function recordMeasurement(User $user, Child $child): bool
    {
        return $this->update($user, $child);
    }

    public function delete(User $user, Child $child): bool
    {
        return $user->isAdmin();
    }
}
