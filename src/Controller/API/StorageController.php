<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StorageController extends AbstractController
{
    public function __construct()
    {
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_get', methods: [Request::METHOD_GET])]
    public function getStorage(
        Request $request,
        string $key
    ): JsonResponse
    {
        return new JsonResponse(

        );
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_post', methods: [Request::METHOD_POST])]
    public function postStorage(
        Request $request,
        string $key
    ): JsonResponse
    {
        return new JsonResponse(

        );
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_patch', methods: [Request::METHOD_PATCH])]
    public function patchStorage(
        Request $request,
        string $key
    ): JsonResponse
    {
        return new JsonResponse(

        );
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_delete', methods: [Request::METHOD_DELETE])]
    public function deleteStorage(
        Request $request,
        string $key
    ): JsonResponse
    {
        return new JsonResponse(

        );
    }


}
