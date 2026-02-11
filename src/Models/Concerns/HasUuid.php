<?php

declare(strict_types=1);

namespace Additions\Models\Concerns;

trait HasUuid
{
    /**
     * Find model by Uuid.
     */
    public static function findByUuid(string $uuid): ?static
    {
        return static::where((new static)->getUuidKeyName(), $uuid)->first();
    }

    /**
     * Get the uuid key name
     */
    public function getUuidKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the uuid value.
     *
     * @return string|int
     */
    public function getUuidKey()
    {
        return $this->getAttribute($this->getUuidKeyName());
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return $this->getUuidKeyName() ?? $this->getKeyName();
    }

    /**
     *  Generate a new unique uuid for this model
     */
    public function newUniqueUuid(): string
    {
        // Ensure the UUID is unique.
        do {
            $uuid = bin2hex(random_bytes(13));
        } while ($this->query()->where($this->getUuidKeyName(), $uuid)->exists());

        return $uuid;
    }

    /**
     * Boot the trait.
     */
    protected static function bootHasUuid()
    {
        static::creating(function (self $model): void {
            if (! $model->getAttribute($model->getUuidKeyName())) {
                $model->setAttribute($model->getUuidKeyName(), $model->newUniqueUuid());
            }
        });
    }
}
