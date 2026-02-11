<?php

declare(strict_types=1);

namespace Additions\Support\Traits;

use LogicException;

trait PreventInstance
{
    /**
     * Prevent the class from being instantiated.
     *
     * @throws LogicException
     */
    public function __construct()
    {
        throw new LogicException(
            sprintf(
                '%s cannot be instantiated. This class is intended for static usage only.',
                static::class
            )
        );
    }
}
