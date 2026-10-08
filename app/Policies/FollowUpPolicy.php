<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Child;
use App\Models\FollowUp;
use App\Models\User;

class FollowUpPolicy
{
    public function view(User $user, FollowUp $followUp): bool
    {
        return $user->can('view', $followUp->child);
    }

    /** Membuat tindak lanjut: kader dan admin, untuk anak yang dapat mereka lihat. */
    public function create(User $user, Child $child): bool
    {
        return ($user->roles === UserRole::Kader || $user->isAdmin()) && $user->can('view', $child);
    }

    /** Mengubah status: penanggung jawab, kader pada anak tersebut, atau admin. */
    public function update(User $user, FollowUp $followUp): bool
    {
        if ($user->isAdmin() || $followUp->assigned_to === $user->id) {
            return true;
        }

        return $user->roles === UserRole::Kader && $user->can('view', $followUp->child);
    }
}
