<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\PaginationService;
use App\Repository\InventoryRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\API\Inventories\InventoryService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class InventoryController extends AbstractController
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly PaginationService $paginationService,
        private readonly InventoryService $inventoryService,
        private readonly CustomResponse $customResponse,
        private readonly CacheInterface $cache
    )
    {
    }

    #[Route('/api/inventories', name: 'api_inventories', methods: [Request::METHOD_GET])]
    public function getInventories(
        Request $request
    ): Response
    {
        $totalAmount = $this->inventoryRepository->count([]);
        $limitAndOffset = $this->paginationService->calculateOffsetAndLimit($totalAmount, $request->query->all());
        $inventories = $this->inventoryRepository->findBy([], ['id' => 'ASC'], $limitAndOffset['limit'], $limitAndOffset['offset']);

        if (count($inventories) === 0) {
            return $this->customResponse->errorResponse($request, 'No inventories here, maybe next time...');
        }

        $normalizedInventories = $this->inventoryService->prepareData($inventories, null, 'api_all');

        return $this->customResponse->payloadResponse($normalizedInventories, [
            'totalPages' => $this->paginationService->getTotalPages(),
            'currentPage' => $this->paginationService->getCurrentPage(),
            'totalAmount' => $totalAmount,
            'currentAmount' => count($normalizedInventories)
        ]);
    }

    #[Route('/api/inventories/{uuid}', name: 'api_inventory_by_uuid', methods: [Request::METHOD_GET])]
    public function getInventoryByUserId(
        Request $request,
        string $uuid
    ): Response
    {
        if ((bool)$request->query->get('filter') === true) {
            $list = $this->inventoryRepository->getInventoryListFromCacheByUuidWithFilter($uuid, $request->query);
            $paginatedList = $this->paginationService->getDataByPage($list, $request->query->all());

            $inventory = $this->inventoryRepository->filterInventoryByList($uuid, $paginatedList);

            return $this->customResponse->payloadResponse($inventory, [
                'totalPages' => $this->paginationService->getTotalPages(),
                'currentPage' => $this->paginationService->getCurrentPage(),
                'totalAmount' => $this->paginationService->getTotalAmount(),
                'currentAmount' => $this->paginationService->getCurrentAmount()
            ]);
        }

        $inventory = json_decode($this->inventoryRepository->getInventoryInJsonFromCacheByUuid($uuid) , true);
        $paginatedInventory = $this->paginationService->getDataByPage($inventory, $request->query->all());

        return $this->customResponse->payloadResponse($paginatedInventory, [
            'totalPages' => $this->paginationService->getTotalPages(),
            'currentPage' => $this->paginationService->getCurrentPage(),
            'totalAmount' => $this->paginationService->getTotalAmount(),
            'currentAmount' => $this->paginationService->getCurrentAmount()
        ]);
    }

    #[Route('/api/inventories/{uuid}/{itemId}', name: 'api_inventory_by_uuid_and_itemId_get', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_GET])]
    public function getInventoryByUuidAndItemId(
        string $uuid,
        int $itemId
    ): Response
    {
        $inventory = $this->inventoryRepository->getItemInInventoryFromCacheByUuidAndItemId($uuid, $itemId);

        return new JsonResponse(
            [
                'data' => $inventory
            ]
        );
    }

    #[Route('/api/inventories/{uuid}/{itemId}', name: 'api_inventory_by_uuid_and_itemId_post', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_POST])]
    public function postInventoryByUuidAndItemId(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $parameter = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (!array_key_exists('amount', $parameter)) {
            return $this->customResponse->errorResponse($request, 'Amount is required with POST method!', 406);
        }

        $this->inventoryService->createEntryInInventory($parameter, $uuid, $itemId);
        $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

        return $this->customResponse->notificationResponse($request, 'Item successfully added to inventory', 201);
    }

    #[Route('/api/inventories/{uuid}/{itemId}', name: 'api_inventory_by_uuid_and_itemId_patch', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function patchInventoryByUuidAndItemId(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $parameter = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

        $this->inventoryService->updateInventory($parameter, $uuid, $itemId);

        return $this->customResponse->notificationResponse($request, 'Inventory updated');
    }

    #[Route('/api/inventories/{uuid}/{itemId}', name: 'api_inventory_by_uuid_and_itemId_delete', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteInventoryByUuidAndItemId(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

        $this->inventoryService->deleteItemFromInventory($uuid, $itemId);

        return $this->customResponse->notificationResponse($request, 'Item successfully removed from inventory');
    }

    #[Route('/api/inventories/{uuid}/{itemId}/parameter', name: 'api_inventory_by_uuid_and_itemId_parameter_get', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_GET])]
    public function getParameterFromItemInInventory(
        string $uuid,
        int $itemId
    ): Response
    {
        $inventory = $this->inventoryRepository->getItemInInventoryFromCacheByUuidAndItemId($uuid, $itemId);

        return new JsonResponse(
            [
                'data' => $inventory['parameter']
            ]
        );
    }

    #[Route('/api/inventories/{uuid}/{itemId}/parameter', name: 'api_inventory_by_uuid_and_itemId_parameter_delete', requirements: ['itemId' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteParameterFromItemInInventory(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (count($parameters) === 0) {
            return $this->customResponse->errorResponse($request, 'Not even passed a parameter for delete. Nothing changed!', 406);
        }

        $inventory = $this->inventoryRepository->getItemInInventoryByUuidAndItemId($uuid, $itemId);

        $this->inventoryService->deleteParameter($inventory, $parameters);
        $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

        return $this->customResponse->notificationResponse($request, sprintf('Inventory parameter from user %s and item %d successfully removed', $itemId, $itemId));
    }


}
