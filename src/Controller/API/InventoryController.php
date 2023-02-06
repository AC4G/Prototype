<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\InventoryRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\API\Inventories\InventoriesService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class InventoryController extends AbstractController
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly InventoriesService $inventoriesService,
        private readonly CustomResponse $customResponse,
        private readonly CacheInterface $cache
    )
    {
    }

    /**
     * @Route("/api/inventories", name="api_inventories", methods={"GET"})
     */
    public function getInventories(
        Request $request
    ): Response
    {
        $inventory = $this->inventoryRepository->findAll();

        if (count($inventory) === 0) {
            return $this->customResponse->errorResponse($request, 'No inventories here, maybe next time...');
        }

        $format = $this->inventoriesService->getFormat($request);

        return new JsonResponse(
            $this->inventoriesService->prepareData($inventory, $format)
        );
    }

    /**
     * @Route("/api/inventories/{uuid}", name="api_inventory_by_uuid", methods={"GET"})
     */
    public function getInventoryByUserId(
        Request $request,
        string $uuid
    ): Response
    {
        $inventory = $this->cache->getItem('inventory_' . $uuid)->get();

        $format = $this->inventoriesService->getFormat($request);

        return new JsonResponse(
            $this->inventoriesService->prepareData($inventory, $format)
        );
    }

    /**
     * @Route("/api/inventories/{uuid}/{itemId}", name="api_inventory_by_uuid_and_itemId", methods={"GET", "POST", "PATCH", "DELETE"}, requirements={"itemId" = "\d+"})
     */
    public function processInventoryByItem(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $user = $this->cache->getItem('user_' . $uuid)->get();

        $parameter = json_decode($request->getContent(), true);

        if (($request->isMethod('POST') || $request->isMethod('PATCH')) && json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        $item = $this->cache->getItem('item_' . $itemId)->get();
        $inventory = '';

        if ($request->isMethod('GET')) {
            $inventory = $this->cache->getItem('inventory_' . $uuid . '_item_' . $itemId)->get();
        }

        if (!$request->isMethod('GET')) {
            $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);
        }

        if ($request->isMethod('GET')) {
            $format = $this->inventoriesService->getFormat($request);

            return new JsonResponse(
                $this->inventoriesService->prepareData($inventory, $format),
            );
        }

        if ($request->isMethod('POST')) {
            if (!array_key_exists('amount', $parameter)) {
                return $this->customResponse->errorResponse($request, 'Amount is required with POST method!', 406);
            }

            $this->inventoriesService->createEntryInInventory($parameter, $user, $item);
            $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

            return $this->customResponse->notificationResponse($request, 'Item successfully added to inventory', 201);
        }

        $this->cache->delete('inventory_' . $uuid . '_item_' . $itemId);

        if ($request->isMethod('PATCH')) {
            $this->inventoriesService->updateInventory($parameter, $inventory);

            return $this->customResponse->notificationResponse($request, 'Inventory updated');
        }

        $this->inventoryRepository->deleteEntry($this->inventoryRepository->findOneBy(['id' => $inventory->getId()]));

        return $this->customResponse->notificationResponse($request, 'Item successfully removed from inventory');
    }

    /**
     * @Route("/api/inventories/{uuid}/{itemId}/parameters", name="api_inventory_by_uuid_and_itemId_parameters", methods={"DELETE", "GET"}, requirements={"itemId" = "\d+"})
     */
    public function processParameterFromItemInInventory(
        Request $request,
        string $uuid,
        int $itemId
    ): Response
    {
        $inventory = '';

        if ($request->isMethod('GET')) {
            $inventory = $this->cache->getItem('inventory_' . $uuid . '_item_' . $itemId);
        }

        if (!$request->isMethod('GET')) {
            $user = $this->cache->getItem('user_' . $uuid)->get();

            $item = $this->cache->getItem('item_' . $itemId)->get();

            $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);
        }

        if ($request->isMethod('GET')) {
            return new JsonResponse(
                json_decode(
                    $inventory->getParameter(),
                    true
                )
            );
        }

        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (count($parameters) === 0) {
            return $this->customResponse->errorResponse($request, 'Not even passed a parameter for delete. Nothing changed!', 406);
        }

        $this->inventoriesService->deleteParameter($inventory, $parameters);
        $this->cache->delete('inventory_' . $user->getId() . '_item_' . $item->getId());

        return $this->customResponse->notificationResponse($request, sprintf('Inventory parameter from user %s and item %d successfully removed', $itemId, $itemId));
    }


}
