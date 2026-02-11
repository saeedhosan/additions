<?php

declare(strict_types=1);

namespace Additions\Support;

class Json
{
    /**
     * Determine whether the given data is a valid JSON string.
     *
     * @param  mixed  $json
     */
    public static function is($json): bool
    {
        if (! is_string($json)) {
            return false;
        }

        json_decode($json);

        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Safely decode the given data to an array or return default.
     *
     * @param  array<mixed>|null  $default
     * @return array<mixed>|null
     */
    public static function decode(?string $data, ?array $default = null): ?array
    {
        if ($data === null) {
            return $default;
        }

        $decoded = json_decode($data, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            return $default;
        }

        return $decoded;
    }
}
