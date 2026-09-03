<?php

namespace App\Services;

use InvalidArgumentException;

class IsbnService
{
    public function normalize(?string $isbn): ?string
    {
        $isbn = trim((string) $isbn);
        if ($isbn === '') {
            return null;
        }

        $normalized = strtoupper(str_replace(['-', ' ', '.'], '', $isbn));
        if (! preg_match('/^(?:\d{9}[\dX]|\d{13})$/', $normalized)) {
            throw new InvalidArgumentException('ISBN harus berupa ISBN-10 atau ISBN-13 yang valid.');
        }

        if (strlen($normalized) === 10 && ! $this->validIsbn10($normalized)) {
            throw new InvalidArgumentException('Checksum ISBN-10 tidak valid.');
        }

        if (strlen($normalized) === 13 && ! $this->validIsbn13($normalized)) {
            throw new InvalidArgumentException('Checksum ISBN-13 tidak valid.');
        }

        return $normalized;
    }

    private function validIsbn10(string $isbn): bool
    {
        $sum = 0;
        for ($index = 0; $index < 10; $index++) {
            $sum += (10 - $index) * ($isbn[$index] === 'X' ? 10 : (int) $isbn[$index]);
        }

        return $sum % 11 === 0;
    }

    private function validIsbn13(string $isbn): bool
    {
        $sum = 0;
        for ($index = 0; $index < 13; $index++) {
            $sum += (int) $isbn[$index] * ($index % 2 === 0 ? 1 : 3);
        }

        return $sum % 10 === 0;
    }
}
