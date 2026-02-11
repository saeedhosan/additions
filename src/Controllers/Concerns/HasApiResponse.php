<?php

declare(strict_types=1);

namespace Additions\Controllers\Concerns;

trait HasApiResponse
{
    /**
     * Send json success message response
     */
    public function message(?string $message, bool $success = true, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    /**
     * Send json success response
     */
    public function success(?string $message, int $status = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
        ], $status);
    }

    /**
     * Send json error response
     */
    public function error(?string $error, int $status = 500)
    {
        return response()->json([
            'success' => false,
            'message' => $error,
        ], $status);
    }

    /**
     * Send json response
     */
    public function json($data): \Illuminate\Http\JsonResponse
    {
        return response()->json($data);
    }
}
