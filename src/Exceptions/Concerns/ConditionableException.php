<?php

declare(strict_types=1);

namespace Additions\Exceptions\Concerns;

use Throwable;

trait ConditionableException
{
    /**
     * Throw the exception returned by the callback if condition is truthy.
     *
     * @param  mixed  $condition
     * @param  callable():Throwable  $callback
     */
    public static function when($condition, callable $callback): void
    {
        if ($condition) {
            throw $callback();
        }
    }

    /**
     * Throw this exception if the condition is true.
     *
     * @param  mixed  $condition
     * @param  mixed  ...$params
     */
    public static function throwIf($condition, ...$params): void
    {
        if ($condition) {
            throw new static(...$params);
        }
    }

    /**
     * Throw this exception unless the condition is true.
     *
     * @param  mixed  $condition
     * @param  mixed  ...$params
     */
    public static function throwUnless($condition, ...$params): void
    {
        if (! $condition) {
            throw new static(...$params);
        }
    }

    /**
     * Alias of throwIf().
     *
     * @param  mixed  $condition
     * @param  mixed  ...$params
     */
    public static function if($condition, ...$params): void
    {
        if ($condition) {
            throw new static(...$params);
        }
    }
}
