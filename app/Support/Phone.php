<?php

namespace App\Support;

class Phone
{
    /** Hanya digit, tanpa spasi atau tanda baca. */
    public static function digits(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone);
    }

    /** Format internasional Indonesia (62...) untuk tautan WhatsApp. */
    public static function international(?string $phone): string
    {
        $digits = self::digits($phone);

        return match (true) {
            $digits === '' => '',
            str_starts_with($digits, '62') => $digits,
            str_starts_with($digits, '0') => '62' . substr($digits, 1),
            default => '62' . $digits,
        };
    }

    public static function whatsappUrl(?string $phone, string $message = ''): ?string
    {
        $number = self::international($phone);

        if ($number === '') {
            return null;
        }

        return 'https://wa.me/' . $number . ($message !== '' ? '?text=' . rawurlencode($message) : '');
    }
}
