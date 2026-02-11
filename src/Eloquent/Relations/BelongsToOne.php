<?php

declare(strict_types=1);

namespace Additions\Eloquent\Relations;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 * @template TPivotModel of \Illuminate\Database\Eloquent\Relations\Pivot = \Illuminate\Database\Eloquent\Relations\Pivot
 * @template TAccessor of string = 'pivot'
 *
 * @extends \Illuminate\Database\Eloquent\Relations\BelongsToMany<TRelatedModel, TDeclaringModel, TPivotModel, TAccessor>
 *
 * @todo use TAccessor when PHPStan bug is fixed: https://github.com/phpstan/phpstan/issues/12756
 */
class BelongsToOne extends BelongsToMany
{
    /**
     * Get single related model for the relationship.
     *
     * @return TRelatedModel|null
     */
    /** @phpstan-ignore-next-line */
    public function getResults()
    {
        /** @phpstan-ignore-next-line */
        return $this->query->first();
    }

    /**
     * {@inheritDoc}
     */
    public function getEager()
    {
        return parent::getEager();
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array<TDeclaringModel>  $models
     * @param  string  $relation
     * @return array<TDeclaringModel>
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, null);
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array<TDeclaringModel>  $models
     * @param  \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model>  $results
     * @param  string  $relation
     * @return array<TDeclaringModel>
     */
    public function match(array $models, Collection $results, $relation)
    {
        $dictionary = [];

        // Use the parent key from the pivot (e.g., blog_id) as the dictionary key
        $parentKeyOnPivot = $this->foreignPivotKey; // e.g. 'blog_id'

        foreach ($results as $result) {
            // Expect pivot is loaded by BelongsToMany eager query
            $parentId = data_get($result, "pivot.{$parentKeyOnPivot}");

            // Keep only the first related model per parent
            if (is_numeric($parentId)) {
                $parentId = (int) $parentId;
                if (! array_key_exists($parentId, $dictionary)) {
                    $dictionary[$parentId] = $result;
                }
            }
        }

        foreach ($models as $model) {
            $key = $model->getKey();
            $result = null;
            if (is_numeric($key)) {
                $key = (int) $key;
                $result = $dictionary[$key] ?? null;
            }
            $model->setRelation($relation, $result);
        }

        return $models;
    }
}
