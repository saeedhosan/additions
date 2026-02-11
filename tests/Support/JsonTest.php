<?php

use Additions\Support\Json;

test('json is', function () {
    // valid json strings
    expect(Json::is('{"name":"Saeed"}'))->toBeTrue();
    expect(Json::is('[1,2,3]'))->toBeTrue();
    expect(Json::is('"string"'))->toBeTrue();
    expect(Json::is('null'))->toBeTrue();

    // invalid or non-string values
    expect(Json::is('{invalid-json}'))->toBeFalse();
    expect(Json::is(''))->toBeFalse();
    expect(Json::is(123))->toBeFalse();
    expect(Json::is(null))->toBeFalse();
    expect(Json::is([]))->toBeFalse();
});

test('json decode', function () {
    $default = ['default' => true];

    // valid json arrays
    expect(Json::decode('{"name":"Saeed"}'))
        ->toBe(['name' => 'Saeed']);

    expect(Json::decode('[1,2,3]'))
        ->toBe([1, 2, 3]);

    // null input
    expect(Json::decode(null, $default))
        ->toBe($default);

    // invalid json
    expect(Json::decode('{invalid-json}', $default))
        ->toBe($default);

    // valid json but not an array
    expect(Json::decode('"string"', $default))
        ->toBe($default);

    expect(Json::decode('123', $default))
        ->toBe($default);

    // empty array
    expect(Json::decode('[]'))
        ->toBe([]);
});
