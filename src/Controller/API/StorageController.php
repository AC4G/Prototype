<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\StorageRepository;
use App\Service\API\Storage\StorageService;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StorageController extends AbstractController
{
    public function __construct(
        private readonly StorageRepository $storageRepository,
        private readonly StorageService $storageService,
        private readonly CustomResponse $customResponse
    )
    {
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_get', methods: [Request::METHOD_GET])]
    public function getStorage(
        Request $request,
        string $key
    ): Response
    {
        $projectId = $request->attributes->get('projectId');
        $storage = json_decode($this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $key)->getValue(), true);

        return $this->customResponse->payloadResponse($storage);
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_post', methods: [Request::METHOD_POST])]
    public function postStorage(
        Request $request,
        string $key
    ): Response
    {
        $projectId = $request->attributes->get('projectId');
        $value = json_decode($request->getContent(), true)['value'];

        $this->storageService->saveStorage($projectId, $key, $value);

        return $this->customResponse->notificationResponse($request, 'Storage saved!');
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_patch', methods: [Request::METHOD_PATCH])]
    public function patchStorage(
        Request $request,
        string $key
    ): Response
    {
        $projectId = $request->attributes->get('projectId');
        $value = json_decode($request->getContent(), true)['value'];

        $this->storageService->updateStorage($projectId, $key, $value);

        return $this->customResponse->notificationResponse($request, 'Storage updated!');
    }

    #[Route('/api/storage/{key}', name: 'api_storage_by_key_delete', methods: [Request::METHOD_DELETE])]
    public function deleteStorage(
        Request $request,
        string $key
    ): Response
    {
        $projectId = $request->attributes->get('projectId');

        $this->storageService->deleteStorage($projectId, $key);

        return $this->customResponse->notificationResponse($request, 'Storage successfully deleted!');
    }


}
