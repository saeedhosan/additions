<?php

use Additions\Support\Traits\CreateInstance;
use Additions\Support\Traits\PreventInstance;

test('CreateInstance', function () {
    $class = new class('default')
    {
        use CreateInstance;

        public function __construct(
            public string $name
        ) {}
    };

    $className = $class::class;

    // make() resolves a new instance
    $firstMake = $className::make('first');
    $secondMake = $className::make('first');

    expect($firstMake)->toBeInstanceOf($className);
    expect($secondMake)->toBeInstanceOf($className);
    expect($firstMake)->not->toBe($secondMake);

    // make() passes constructor arguments
    expect($firstMake->name)->toBe('first');

    // init() caches by arguments
    $firstInit = $className::init('cached');
    $secondInit = $className::init('cached');

    expect($firstInit)->toBe($secondInit);
    expect($firstInit->name)->toBe('cached');

    // init() returns different instances for different arguments
    $otherInit = $className::init('different');

    expect($otherInit)->not->toBe($firstInit);
    expect($otherInit->name)->toBe('different');
});

test('PreventInstance', function () {
    $class = new class
    {
        use PreventInstance;
    };

    $className = $class::class;

    expect(fn () => new $className)->toThrow(
        LogicException::class,
        "{$className} cannot be instantiated. This class is intended for static usage only."
    );
})->throws(LogicException::class);
