<?php

declare(strict_types=1);

namespace Additions\Models\Concerns;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * Custom Eloquent builder that uses the model's route key in `find()`.
 *
 * @template TModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends EloquentBuilder<TModel>
 */
class RouteKeyBuilder extends EloquentBuilder
{
    /**
     * Get the model by route key instead of primary key.
     *
     * {@inheritDoc}
     *
     * @param  array<int, string>  $columns
     */
    public function find($id, $columns = ['*'])
    {
        return $this->where($this->getModel()->getRouteKeyName(), $id)->first($columns);
    }
}

/**
 * Trait HasRouteBinding
 *
 * Makes `find()` use the model's route key instead of the primary key.
 */
trait HasRouteBinding
{
    /**
     * Create a new Eloquent query builder for the model.
     *
     * @param  \Illuminate\Database\Query\Builder  $query
     * @return RouteKeyBuilder<static>
     */
    public function newEloquentBuilder($query): EloquentBuilder
    {
        return new RouteKeyBuilder($query);
    }
}
