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

    /**
     * Edit the value of an existing key in the .env file.
     */
    public static function editKey(string $key, string $value): bool
    {
        if (! File::exists(static::envPath())) {
            return false;
        }

        $content = File::get(static::envPath());

        // Quote the value if it contains spaces, quotes, or special chars
        $value = '"'.addcslashes($value, '"').'"';

        $pattern = "/^{$key}=.*$/m";

        if (preg_match($pattern, $content)) {

            $content = preg_replace($pattern, "{$key}={$value}", $content);

            /** @phpstan-ignore-next-line */
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

        // ALWAYS quote
        $value = '"'.addcslashes($value, '"').'"';

        $line = PHP_EOL."{$key}={$value}".PHP_EOL;

        /** @phpstan-ignore-next-line */
        return File::append(static::envPath(), $line) !== false;
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
     *
     * Useful to reflect changes without clearing cached config files manually.
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
     *
     * @param  string  $key
     */
    public static function put($key, ?string $value = null): void
    {

        if (self::keyExists($key)) {
            /** @phpstan-ignore-next-line */
            self::editKey($key, $value);
        } else {
            /** @phpstan-ignore-next-line */
            self::addKey($key, $value);
        }
    }

    /**
     * Get the current environment file path.
     *
     * Uses Laravel's built-in method to ensure it works
     * even if the `.env` file is renamed.
     */
    protected static function envPath(): string
    {
        return app()->environmentFilePath();
    }
}
