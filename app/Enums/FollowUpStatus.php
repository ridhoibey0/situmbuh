<?php

namespace App\Enums;

enum FollowUpStatus: string
{
    case Open = 'open';
    case InProgress = 'in_progress';
    case Done = 'done';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Belum dikerjakan',
            self::InProgress => 'Sedang berjalan',
            self::Done => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'secondary',
            self::InProgress => 'primary',
            self::Done => 'success',
            self::Cancelled => 'dark',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Open, self::InProgress], true);
    }

    /** @return list<self> */
    public static function active(): array
    {
        return [self::Open, self::InProgress];
    }
}
