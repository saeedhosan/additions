<?php

declare(strict_types=1);

namespace Additions\Models\Concerns;

/**
 * @internal
 */
trait HasStaticAccess
{
    /**
     * Create a new static instance
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public static function instance()
    {
        return new static;
    }

    /**
     * Get the table name statically from the model.
     */
    public static function tableName(): string
    {
        return static::instance()->getTable();
    }

    /**
     * Get the route key name statically from the model.
     */
    public static function routeKeyName(): string
    {
        return static::instance()->getRouteKeyName();
    }

    /**
     * Get the fillable attributes statically from the model.
     */
    public static function fields(): array
    {
        return static::instance()->getFillable();
    }

    /**
     * Get model find by route key
     *
     * @param  string|int  $key
     */
    public static function findByKey($key): ?static
    {
        return static::where(static::routeKeyName(), $key)->first();
    }

    /**
     * Get model find by route key
     *
     * @param  string|int  $key
     */
    public static function findByRouteKey($key): ?static
    {
        return static::findByKey($key);
    }
}
