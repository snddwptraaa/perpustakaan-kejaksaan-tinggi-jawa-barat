<?php

namespace App\Support;

/**
 * Normalisasi ISBN ke bentuk kanonik (tanpa separator, uppercase).
 *
 * Menerima input yang mengandung dash/spasi/titik; mengembalikan bentuk
 * ternormalisasi jika lolos checksum ISBN-10 / ISBN-13, atau string kosong
 * bila input kosong. Melempar InvalidArgumentException untuk input lain —
 * caller diharapkan mengubahnya menjadi ValidationException.
 */
final class Isbn
{
    public static function normalize(?string $isbn): string
    {
        $isbn = trim((string) $isbn);
        if ($isbn === '') {
            return '';
        }

        $normalized = strtoupper(str_replace(['-', ' ', '.'], '', $isbn));

        if (! preg_match('/^(?:\d{9}[\dX]|\d{13})$/', $normalized)) {
            throw new \InvalidArgumentException('ISBN harus berupa 10 digit (ISBN-10) atau 13 digit (ISBN-13).');
        }

        if (strlen($normalized) === 10 && ! self::isValidIsbn10($normalized)) {
            throw new \InvalidArgumentException('Checksum ISBN-10 tidak valid.');
        }

        if (strlen($normalized) === 13 && ! self::isValidIsbn13($normalized)) {
            throw new \InvalidArgumentException('Checksum ISBN-13 tidak valid.');
        }

        return $normalized;
    }

    private static function isValidIsbn10(string $isbn): bool
    {
        $sum = 0;
        for ($i = 0; $i < 10; $i++) {
            $digit = $isbn[$i] === 'X' ? 10 : (int) $isbn[$i];
            $sum += (10 - $i) * $digit;
        }

        return $sum % 11 === 0;
    }

    private static function isValidIsbn13(string $isbn): bool
    {
        $sum = 0;
        for ($i = 0; $i < 13; $i++) {
            $sum += (int) $isbn[$i] * ($i % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }
}
