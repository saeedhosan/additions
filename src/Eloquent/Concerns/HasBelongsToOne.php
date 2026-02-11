<?php

declare(strict_types=1);

namespace Additions\Eloquent\Concerns;

use Additions\Eloquent\Relations\BelongsToOne;

trait HasBelongsToOne
{
    /**
     * Create the relations
     */
    protected function belongsToOne(
        string $related,
        string $table,
        string $foreignPivotKey,
        string $relatedPivotKey,
        ?string $parentKey = null,
        ?string $relatedKey = null,
        ?string $relation = null
    ): BelongsToOne {
        $instance = $this->newRelatedInstance($related);

        $relation = in_array($relation, [null, '', '0'], true) ? $this->guessBelongsToManyRelation() : $relation;

        $foreignPivotKey = $foreignPivotKey !== '' && $foreignPivotKey !== '0' ? $foreignPivotKey : $this->getForeignKey();
        $relatedPivotKey = $relatedPivotKey !== '' && $relatedPivotKey !== '0' ? $relatedPivotKey : $instance->getForeignKey();

        return new BelongsToOne(
            $instance->newQuery(),
            $this,
            $table,
            $foreignPivotKey,
            $relatedPivotKey,
            in_array($parentKey, [null, '', '0'], true) ? $this->getKeyName() : $parentKey,
            in_array($relatedKey, [null, '', '0'], true) ? $instance->getKeyName() : $relatedKey,
            $relation
        );
    }
}
