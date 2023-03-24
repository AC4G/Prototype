<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use App\Repository\InventoryRepository;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIInventoryListenerService
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly SecurityService $securityService,
        private readonly UserRepository $userRepository,
        private readonly CustomResponse $customResponse,
        private readonly ItemRepository $itemRepository
    )
    {
    }

    public function validateJWTAndParameterForInventoryController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $event->getRequest()->attributes->set('scopes', $accessToken['scopes']);

        if ($this->isRouteInventories($event, $accessToken, $params)) {
            return;
        }

        $uuid = $params['uuid'];

        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with uuid %s doesn\'t exists!', $uuid), 404));

            return;
        }

        if ($this->isRouteInventoryByUuid($event, $accessToken, $params, $user)) {
            return;
        }

        $itemId = (int)$params['itemId'];

        $item = json_decode($this->itemRepository->getItemFromCacheInJsonFormatById($itemId), true);

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item doesn\'t exists!', 404));

            return;
        }

        if (($user->isPrivate() && !$this->securityService->hasClientPermissionForInventoryAction($accessToken, $user, $event->getRequest(), $item) || !$user->isPrivate() && !$event->getRequest()->isMethod('GET') && !$this->securityService->hasClientPermissionForInventoryAction($accessToken, $user, $event->getRequest(), $item))) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        if ($event->getRequest()->isMethod('GET')) {
            $inventory = $this->inventoryRepository->getItemInInventoryFromCacheByUuidAndItemId($uuid, $itemId);

            if (is_null($inventory)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User has no item with id %s in inventory!', $itemId), 404));
            }

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

        if ($user->isPrivate() && !$this->securityService->hasClientPermissionForInventoryAction($accessToken, $user, $event->getRequest(), null)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return true;
        }

        $inventory = json_decode($this->inventoryRepository->getInventoryInJsonFromCacheByUuid($user->getUuid()), true);

        if (count($inventory) === 0) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'User has not an item in inventory yet!', 404));
        }

        return true;
    }


}
