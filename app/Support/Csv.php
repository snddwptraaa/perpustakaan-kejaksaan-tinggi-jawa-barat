<?php

namespace App\Support;

final class Csv
{
    public static function safeCell(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if (preg_match('/^[\x00-\x20]*[=+\-@]/u', $value) === 1) {
            return "'".$value;
        }

        return $value;
    }
}
