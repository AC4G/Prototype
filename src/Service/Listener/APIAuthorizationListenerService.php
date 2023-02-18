<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Entity\Item;
use App\Entity\User;
use App\Service\API\UserService;
use App\Repository\ItemRepository;
use App\Serializer\ItemNormalizer;
use App\Service\API\Item\ItemService;
use App\Repository\PublicKeyRepository;
use App\Repository\InventoryRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIAuthorizationListenerService
{
    public function __construct(
        private readonly PublicKeyRepository $publicKeyRepository,
        private readonly InventoryRepository $inventoryRepository,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly ItemRepository $itemRepository,
        private readonly ItemNormalizer $itemNormalizer,
        private readonly UserService $userService,
        private readonly ItemService $itemService,
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

        if (count($params) === 0) {
            return;
        }

        if (array_key_exists('uuid', $params)) {
            return;
        }

        if (!array_key_exists('id', $params)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item id not passed in URI!', 400));

            return;
        }

        $id = $params['id'];

        $item = $this->cache->getItem('item_' . $id)->get();

        if (is_null($item)) {
            $item = $this->itemRepository->findOneBy(['id' => $id]);

            if (is_null($item)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

                return;
            }
        }

        if ($event->getRequest()->isMethod('GET') && $item instanceof Item) {
            if (str_contains($event->getRequest()->attributes->get('_route'), 'parameter')) {
                $itemParameter = $this->cache->getItem('item_' . $id . '_parameter');

                $itemParameter->expiresAfter(86400);
                $itemParameter->set($item->getParameter());
                $this->cache->save($itemParameter);

                return;
            }

            $itemCache = $this->cache->getItem('item_' . $id);

            $itemCache->expiresAfter(86400);
            $itemCache->set(json_encode($this->itemNormalizer->normalize($item)));
            $this->cache->save($itemCache);

            return;
        }

        if (is_string($item)) {
            $item = json_decode($item, true);
        }

        if ($item instanceof Item) {
            $item = $this->itemNormalizer->normalize($item);
        }

        if (!$event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAdjustmentOnItem($accessToken, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
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

        $uuid = $params['uuid'];

        $user = $this->userService->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if ($this->isRouteInventoryByUuid($event, $accessToken, $params, $user)) {
            return;
        }

        $itemId = $params['itemId'];

        $item = json_decode($this->itemService->getItemFromCacheById($itemId), true);

        if (($user->isPrivate() && !$this->securityService->hasClientPermissionForAdjustmentOnUserInventory($accessToken, $user, $item) || !$user->isPrivate() && !$event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAdjustmentOnUserInventory($accessToken, $user, $item))) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if ($event->getRequest()->isMethod('GET')) {
            $this->cache->get('inventory_' . $uuid . '_item_' . $itemId, function (ItemInterface $cacheItem) use ($user, $item) {
                $cacheItem->expiresAfter(86400);

                return $this->inventoryRepository->findOneBy(['user' => $user->getId(), 'item' => $item]);
            });

            return;
        }

        $inventory = $this->inventoryRepository->findOneBy(['user' => $user->getId(), 'item' => $item]);

        if (is_null($inventory) && $event->getRequest()->isMethod('PATCH')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User has no item with id %s in inventory yet! Please use POST method to add item!', $itemId), 406));

            return;
        }

        if (!is_null($inventory) && $event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User already has item with id %s in inventory. For update use PATCH method', $itemId), 406));

            return;
        }

        if (is_null($inventory) && !$event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User has no item with id %s in inventory yet!', $itemId), 406));
        }
    }


    private function isRouteInventories(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): bool
    {
        if (count($params) !== 0) {
            return false;
        }

        if (!$this->securityService->isClientAdmin($accessToken)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return true;
        }

        return true;
    }

    private function isRouteInventoryByUuid(
        RequestEvent $event,
        array $accessToken,
        array $params,
        User $user
    ): bool
    {
        if (count($params) > 1) {
            return false;
        }

        if ($user->isPrivate() && !$this->securityService->hasClientPermissionForAccessingUserRelatedData($accessToken, $user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return true;
        }

        $inventory = $this->cache->get('inventory_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->inventoryRepository->findBy(['user' => $user->getUuid()]);
        });

        if (count($inventory) === 0) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'User has not an item in inventory yet!', 404));
        }

        return true;
    }

    public function validateJWTForUserController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $uuid = $params['uuid'];

        $user = $this->userService->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if (!$this->securityService->hasClientPermissionForAccessingUserRelatedData($accessToken, $user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }

    public function validateJWTForPublicKeyController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $uuid = $params['uuid'];

        $user = $this->userService->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if ($event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForAccessingUserRelatedData($accessToken, $user) || $accessToken['project']['id'] !== 1) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        $publicKey = $this->cache->get('public_key_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->publicKeyRepository->findOneBy(['user' => $user]);
        });

        if (is_null($publicKey) && !$event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Public key not found', 404));

            return;
        }

        if (!is_null($publicKey) && $event->getRequest()->isMethod('POST')) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s has already a public key', $uuid), 400));

            return;
        }

        if ($event->getRequest()->isMethod('POST') || $event->getRequest()->isMethod('PATCH')) {
            $content = json_decode($event->getRequest()->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Invalid Json!', 406));

                return;
            }

            if (!array_key_exists('key', $content)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Key not provided!', 406));

                return;
            }

            if (preg_match('^ssh-rsa AAAA[0-9A-Za-z+/]+[=]{0,3}( [^@]+@[^@]+)?$', $content['key']) === false) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Key is not provided in OpenSSH format!', 406));
            }
        }
    }


}
