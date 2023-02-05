<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Entity\User;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Repository\InventoryRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

use App\Serializer\ItemNormalizer;

final class APIAuthorizationListenerService
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly ItemRepository $itemRepository,
        private readonly UserRepository $userRepository,
        private readonly ItemNormalizer $itemNormalizer,
        private readonly CacheInterface $cache
    )
    {
    }

    public function validateJWTForItemController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        if (!is_null($accessToken['user']['id'])) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Access token is not based on client credentials grant!'. 400));

            return;
        }

        if ($this->isRouteItems($params)) {
            return;
        }

        if (array_key_exists('userId', $params)) {
            return;
        }

        $id = $params['id'];

        $item = $this->cache->getItem('item_' . $id)->get();

        if (!is_null($item)) {
            return;
        }

        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if (str_contains($event->getRequest()->attributes->get('_route'), 'parameter')) {
            $itemParameter = $this->cache->getItem('item_' . $id . '_parameter');

            $itemParameter->expiresAfter(86400);
            $itemParameter->set($item->getParameter());
            $this->cache->save($itemParameter);

            if ($event->getRequest()->isMethod('GET')) {
                return;
            }
        }

        if ($event->getRequest()->isMethod('GET')) {
            $itemCache = $this->cache->getItem('item_' . $id);

            $itemCache->expiresAfter(86400);
            $itemCache->set(json_encode($this->itemNormalizer->normalize($item)));
            $this->cache->save($itemCache);

            return;
        }

        if (!$event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAdjustmentOnItem($accessToken, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }

    private function isRouteItems(
        array $params
    ): bool
    {
        return count($params) === 0;
    }

    public function validateJWTForInventoryController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        if ($this->isRouteInventories($event, $accessToken, $params)) {
            return;
        }

        $userId = $params['userId'];

        $user = $this->cache->get('user_'. $userId, function (ItemInterface $item) use ($userId) {
            $item->expiresAfter(86400);

            return $this->userRepository->findOneBy(['id' => $userId]);
        });

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with id %s doesn\'t exists!', $userId), 404));

            return;
        }

        if ($this->isRouteInventoryByUserId($event, $accessToken, $params, $user)) {
            return;
        }

        $item = $this->cache->get('item_' . $params['itemId'], function (ItemInterface $item) use ($params) {
            $item->expiresAfter(86400);

            return $this->itemRepository->findOneBy(['id' => $params['itemId']]);
        });

        if (($user->isPrivate() && !$this->securityService->hasClientPermissionForAdjustmentOnUserInventory($accessToken, $user, $item) || !$user->isPrivate() && !$event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAdjustmentOnUserInventory($accessToken, $user, $item))) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        $inventory = $this->cache->get('inventory_' . $userId . '_item_' . $params['itemId'], function (ItemInterface $itemInterface) use ($user, $item) {
            $itemInterface->expiresAfter(86400);

            return $this->inventoryRepository->findOneBy(['user' => $user->getId(), 'item' => $item]);
        });

        if (is_null($inventory) && $event->getRequest()->isMethod('PATCH')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User has no item with id %s in inventory yet! Please use POST method to add item!', $params['itemId']), 406));

            return;
        }

        if (!is_null($inventory) && $event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User already has item with id %s in inventory. For update use PATCH method', $params['itemId']), 406));

            return;
        }

        if (is_null($inventory)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User has no item with id %s in inventory yet!', $params['itemId']), 406));
        }
    }


    private function isRouteInventories(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): bool
    {
        if (count($params) === 0 && $this->securityService->isClientAdmin($accessToken)) {
            return true;
        }

        if ((count($params) === 0) && !$this->securityService->isClientAdmin($accessToken)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return true;
        }

        return false;
    }

    private function isRouteInventoryByUserId(
        RequestEvent $event,
        array $accessToken,
        array $params,
        User $user
    ): bool
    {
        if (count($params) > 1) {
            return false;
        }

        if ($user->isPrivate() && !$this->securityService->hasClientPermissionForAccessingInventory($accessToken, $user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return true;
        }

        $inventory = $this->cache->get('inventory_' . $params['userId'], function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->inventoryRepository->findBy(['user' => $user->getId()]);
        });

        if (count($inventory) === 0) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'User has not an item in inventory yet!', 404));
        }

        return true;
    }


}
