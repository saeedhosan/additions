<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Additions\Controllers\Concerns\HasApiResponse;

function makeController()
{
    return new class extends Controller
    {
        use HasApiResponse;
    };
}

it('returns success response', function () {
    $response = makeController()->success('OK');

    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
    expect($response->getData(true))->toBe([
        'success' => true,
        'message' => 'OK',
    ]);
});

it('returns error response', function () {
    $response = makeController()->error('Failed', 400);

    expect($response->getStatusCode())->toBe(400);
    expect($response->getData(true))->toBe([
        'success' => false,
        'message' => 'Failed',
    ]);
});

it('returns raw json response', function () {
    $data = ['foo' => 'bar'];

    $response = makeController()->json($data);

    expect($response->getStatusCode())->toBe(200);
    expect($response->getData(true))->toBe($data);
});
