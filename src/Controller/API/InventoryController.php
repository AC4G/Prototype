<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
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
        private readonly ItemRepository $itemRepository,
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

        if (count($inventory) !== 0) {
            return new JsonResponse(
                $this->inventoriesService->prepareData($inventory)
            );
        }

        return $this->customResponse->errorResponse($request, 'No inventories here, maybe next time...');
    }

    /**
     * @Route("/api/inventories/{userId}", name="api_inventory_by_userId", methods={"GET"}, requirements={"userId" = "\d+"})
     */
    public function getInventoryByUserId(
        Request $request,
        string $userId
    ): Response
    {
        $user = $this->cache->get('user_' . $userId, function () {
            return null;
        });

        if (is_null($user)) {
            return  $this->customResponse->errorResponse($request, 'Internal error, retry again!', 500);
        }

        $inventory = $this->inventoryRepository->findBy(['user' => $user]);

        if (count($inventory) === 0) {
            return $this->customResponse->errorResponse($request, 'User has not an item in inventory yet!', 400);
        }

        return new JsonResponse(
            $this->inventoriesService->prepareData($inventory)
        );
    }

    /**
     * @Route("/api/inventories/{userId}/{itemId}", name="api_inventory_by_userId_and_itemId", methods={"GET", "POST", "PATCH", "DELETE"}, requirements={"userId" = "\d+", "itemId" = "\d+"})
     */
    public function processInventoryByItem(
        Request $request,
        string $userId,
        int $itemId
    ): Response
    {
        $user = $this->cache->get('user_' . $userId, function () {
            return null;
        });

        if (is_null($user)) {
            return  $this->customResponse->errorResponse($request, 'Internal error, retry again!', 500);
        }

        $item = $this->itemRepository->findOneBy(['id' => $itemId]);

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request, 'Item not found', 404);
        }

        $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);

        $parameter = json_decode($request->getContent(), true);

        if (($request->isMethod('POST') || $request->isMethod('PATCH')) && json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (is_null($inventory) && $request->isMethod('PATCH')) {
            return $this->customResponse->errorResponse($request, 'User does not has this item in inventory. Please use POST method to add item!', 406);
        }

        if (is_null($inventory) && $request->isMethod('POST')) {
            return $this->customResponse->errorResponse($request, sprintf('User already has that item with id %s. For update use PATCH method', $itemId), 406);
        }

        if (is_null($inventory) && ($request->isMethod('GET') || $request->isMethod('DELETE'))) {
            return $this->customResponse->errorResponse($request, 'User does not has this item in inventory!', 406);
        }

        if ($request->isMethod('PATCH')) {
            $this->inventoriesService->updateInventory($parameter, $inventory);

            return $this->customResponse->notificationResponse($request, 'Inventory updated');
        }

        if ($request->isMethod('POST')) {
            if (!array_key_exists('amount', $parameter)) {
                return $this->customResponse->errorResponse($request, 'Amount is required with POST method!', 406);
            }

            $this->inventoriesService->createEntryInInventory($parameter, $user, $item);

            return $this->customResponse->notificationResponse($request, 'Item successfully added to inventory', 201);
        }

        if ($request->isMethod('GET')) {
            return new JsonResponse(
              $this->inventoriesService->prepareData($inventory)
            );
        }

        $this->inventoryRepository->deleteEntry($inventory);

        return $this->customResponse->notificationResponse($request, 'Item successfully removed from inventory');
    }

    /**
     * @Route("/api/inventories/{userId}/{itemId}/parameters", name="api_inventory_by_userId_and_itemId_parameters", methods={"DELETE", "GET"}, requirements={"userId" = "\d+", "itemId" = "\d+"})
     */
    public function processParameterFromItemInInventory(
        Request $request,
        string $userId,
        int $itemId
    ): Response
    {
        $user = $this->cache->get('user_' . $userId, function () {
            return null;
        });

        if (is_null($user)) {
            return  $this->customResponse->errorResponse($request, 'Internal error, retry again!', 500);
        }

        $item = $this->itemRepository->findOneBy(['id' => $itemId]);

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request, sprintf('Item with id %s don\'t exists!', $itemId), 404);
        }

        $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);

        if (is_null($inventory)) {
            return $this->customResponse->errorResponse($request, sprintf('User don\'t has item with id %s in inventory', $itemId), 404);
        }

        if ($request->isMethod('GET')) {
            return new JsonResponse(json_decode($inventory->getParameter(), true));
        }

        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (count($parameters) === 0) {
            return $this->customResponse->errorResponse($request, 'Not even passed a parameter for delete. Nothing changed!', 406);
        }

        $this->inventoriesService->deleteParameter($inventory, $parameters);

        return $this->customResponse->notificationResponse($request, sprintf('Inventory parameter from user %s and item %d successfully removed', $property, $itemId));
    }


}
