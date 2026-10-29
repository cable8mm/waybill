<?php

namespace Cable8mm\Waybill\Support;

/** Applies the personal-information masking rules in Hanjin's print guide. */
final class HanjinDataMasker
{
    /** @param array<string, mixed> $data */
    public static function mask(array $data): array
    {
        foreach (['sender', 'receiver'] as $side) {
            if (! isset($data[$side]) || ! is_array($data[$side])) {
                continue;
            }

            if (isset($data[$side]['name'])) {
                $data[$side]['name'] = self::name((string) $data[$side]['name']);
            }

            if (isset($data[$side]['phone'])) {
                $data[$side]['phone'] = self::phone((string) $data[$side]['phone']);
            }

            if (isset($data[$side]['address'])) {
                $data[$side]['address'] = self::address((string) $data[$side]['address']);
            }
        }

        return $data;
    }

    private static function name(string $name): string
    {
        $characters = preg_split('//u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $length = count($characters);

        if ($length < 2) {
            return $name;
        }

        if (preg_match('/\p{Hangul}/u', $name) === 1) {
            $indices = match (true) {
                $length <= 3 => [1],
                $length === 4 => [1, 3],
                default => array_merge([1], range(3, $length - 1)),
            };
        } else {
            $indices = $length >= 5 ? range(4, $length - 1) : [$length - 1];
        }

        foreach ($indices as $index) {
            $characters[$index] = '*';
        }

        return implode('', $characters);
    }

    private static function phone(string $phone): string
    {
        $digitCount = preg_match_all('/\d/', $phone);
        if ($digitCount === false || $digitCount === 0) {
            return $phone;
        }

        $maskFrom = max(0, $digitCount - 4);
        $digitIndex = 0;

        return preg_replace_callback('/\d/', static function (array $matches) use (&$digitIndex, $maskFrom): string {
            return $digitIndex++ >= $maskFrom ? '*' : $matches[0];
        }, $phone) ?? $phone;
    }

    private static function address(string $address): string
    {
        if (preg_match('/^(.+?(?:대로|로|길)\s*\d+)(?:\s+.*)?$/u', $address, $matches) === 1) {
            return $matches[1].' ****';
        }

        if (preg_match('/^(.+?(?:읍|면|동))(?:\s+.*)?$/u', $address, $matches) === 1) {
            return $matches[1].' ****';
        }

        $parts = preg_split('/\s+/u', trim($address), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return implode(' ', array_slice($parts, 0, 3)).' ****';
    }
}
