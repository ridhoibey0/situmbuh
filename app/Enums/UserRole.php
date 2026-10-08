<?php

namespace App\Enums;

enum UserRole: string
{
    case Superadmin = 'superadmin';
    case Admin = 'admin';
    case Parent = 'user';
    case Kader = 'kader';
    case Nakes = 'nakes';

    public function label(): string
    {
        return match ($this) {
            self::Superadmin => 'Super Admin',
            self::Admin => 'Admin',
            self::Parent => 'Orang Tua',
            self::Kader => 'Kader',
            self::Nakes => 'Tenaga Kesehatan',
        };
    }

    public function isAdmin(): bool
    {
        return in_array($this, [self::Superadmin, self::Admin], true);
    }

    /** Kader dan nakes: pengguna layanan yang menangani banyak anak. */
    public function isStaff(): bool
    {
        return in_array($this, [self::Kader, self::Nakes], true);
    }
}
