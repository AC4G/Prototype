<?php declare(strict_types=1);

namespace App\Service\Response\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CustomResponse
{
    public function errorResponse(
        Request $request,
        string $message,
        int $status = 200,
        array $headers = []
    ): JsonResponse
    {
        $data = [
            'error' => [
                'status' => $status,
                'source' => [
                    'pointer' => $request->getRequestUri()
                ],
                'method' => $request->getMethod(),
                'message' => $message
            ]
        ];

        return new JsonResponse(
            $data,
            $status
        );
    }

    public function notificationResponse(
        Request $request,
        string $message,
        int $status = 200,
        array $headers = []
    ): JsonResponse
    {
        $data = [
            'notification' => [
                'status' => $status,
                'source' => [
                    'pointer' => $request->getRequestUri()
                ],
                'method' => $request->getMethod(),
                'message' => $message
            ]
        ];

        return new JsonResponse(
            $data,
            $status,
            $headers
        );
    }

    public function payloadResponse(
        array|int|string $payload,
        array $meta = null,
        int $status = 200,
        array $header = [],
    ): JsonResponse
    {
        $data = ['data' => $payload];

        if (!is_null($meta)) {
            $data = array_merge($data, ['meta' => $meta]);
        }

        return new JsonResponse(
            $data,
            $status,
            $header
        );
    }


}
