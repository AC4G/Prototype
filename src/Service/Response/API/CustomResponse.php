<?php declare(strict_types=1);

namespace App\Service\Response\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CustomResponse
{
    public function errorResponse(
        Request $request,
        string $message,
        int $status = 200
    ): JsonResponse
    {
        $data = [
            'error' => [
                'status' => $status,
                'source' => [
                    'pointer' => $request->getUri()
                ],
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
        int $status = 200
    ): JsonResponse
    {
        $data = [
            'notification' => [
                'status' => $status,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => $message
            ]
        ];

        return new JsonResponse(
            $data,
            $status
        );
    }


}