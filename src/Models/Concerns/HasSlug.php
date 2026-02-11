<?php

declare(strict_types=1);

namespace Additions\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Whether to update the slug when the source field is dirty.
     */
    protected bool $shouldSlugDirty = true;

    /**
     * The name of the slug key column.
     */
    protected string $slugKeyName = 'slug';

    /**
     * The name of the source column for generating slugs.
     */
    protected string $slugSourceName = 'name';

    /**
     * Find a model by slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::where((new static)->getSlugKeyName(), $slug)->first();
    }

    /**
     * Get the slug key name.
     */
    public function getSlugKeyName(): string
    {
        return $this->slugKeyName;
    }

    /**
     * Get the source field for generating slugs.
     */
    public function getSlugSourceName(): string
    {
        return $this->slugSourceName;
    }

    /**
     * Get the source field for generating slugs.
     */
    public function getSlugSource(): ?string
    {
        return $this->getAttribute($this->getSlugSourceName());
    }

    /**
     * Generate a new unique slug for the model.
     */
    public function generateUniqueSlug(): string
    {
        $slug = Str::slug((string) $this->getSlugSource());

        if ($this->where($this->getSlugKeyName(), $slug)->doesntExist()) {
            return $slug;
        }

        $counter = 1;

        while ($this->where($this->getSlugKeyName(), $slug)->exists()) {
            $slug = "{$slug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * Boot the HasSlug trait.
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function (Model $model): void {
            if (empty($model->getAttribute($model->getSlugKeyName()))) {
                $model->setAttribute($model->getSlugKeyName(), $model->generateUniqueSlug());
            }
        });

        static::updating(function (Model $model): void {
            if ($model->shouldRegenerateSlug() && $model->isDirty($model->getSlugSourceName())) {
                $model->setAttribute($model->getSlugKeyName(), $model->generateUniqueSlug());
            }
        });
    }

    /**
     * Determine if the slug should be regenerated on update.
     */
    protected function shouldRegenerateSlug(): bool
    {
        return $this->shouldSlugDirty;
    }
}
