<?php

declare(strict_types=1);

namespace Additions\Support;

use Illuminate\Support\Facades\File;

class EnvEditor
{
    /**
     * Check if a key exists in the .env file.
     */
    public static function keyExists(string $key): bool
    {
        if (! File::exists(static::envPath())) {
            return false;
        }

        $content = File::get(static::envPath());

        return preg_match("/^{$key}=.*$/m", $content) === 1;
    }

    public static function editKey(string $key, string $value): bool
    {
        if (! File::exists(static::envPath())) {
            return false;
        }

        $content = File::get(static::envPath());

        $quotedValue = '"'.addcslashes($value, '"').'"';

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $content)) {

            $content = (string) preg_replace($pattern, "{$key}={$quotedValue}", $content);

            File::put(static::envPath(), $content);

            return true;
        }

        return false;
    }

    /**
     * Add a new key=value pair to the .env file.
     */
    public static function addKey(string $key, string $value): bool
    {
        if (! File::exists(static::envPath())) {
            File::put(static::envPath(), '');
        }

        $quotedValue = '"'.addcslashes($value, '"').'"';

        $line = PHP_EOL."{$key}={$quotedValue}".PHP_EOL;

        /** @var int|false $result */
        $result = File::append(static::envPath(), $line);

        return (bool) $result;
    }

    /**
     * Set a key in the .env file.
     *
     * Will edit the key if it exists, or add it if missing.
     */
    public static function setKey(string $key, string $value): bool
    {
        return static::keyExists($key)
            ? static::editKey($key, $value)
            : static::addKey($key, $value);
    }

    /**
     * Optionally reload Laravel's configuration in memory.
     */
    public static function reloadConfig(): void
    {
        if (function_exists('app')) {
            // Clear cached config in memory
            /** @phpstan-ignore-next-line */
            app()->make(\Illuminate\Contracts\Config\Repository::class)->set(null);
        }
    }

    /**
     * Gets the value of an environment variable.
     */
    public static function put(string $key, string $value): void
    {
        if (self::keyExists($key)) {
            self::editKey($key, $value);
        } else {
            self::addKey($key, $value);
        }
    }

    /**
     * Get the current environment file path.
     */
    public static function envPath(): string
    {
        return app()->environmentFilePath();
    }
}
