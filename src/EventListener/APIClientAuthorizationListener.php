<?php declare(strict_types=1);

namespace App\EventListener;

use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class APIClientAuthorizationListener implements EventSubscriberInterface
{

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly ItemRepository $itemRepository,
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function onKernelRequest(
        RequestEvent $event
    )
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (!str_starts_with((string)$route, 'api_')) {
            return;
        }

        $jwt = $request->headers->get('Authorization');
        $params = $request->attributes->get('_route_params');

        if (is_null($jwt)) {
            $event->setResponse($this->customResponse->errorResponse($request, 'No JWT passed!', 403));

            return;
        }

        $payload = $this->securityService->decodeJWTAndReturnPayload($jwt);

        if (is_null($payload)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

            return;
        }

        $accessToken = json_decode($payload, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Corrupted access token, retry again or create a new one!', 500));

            return;
        }

        if (str_contains($route, 'item')) {
            $this->validateJWTForItemController($event, $accessToken, $params);

            return;
        }

        if (str_contains($route, 'inventory') || str_contains($route, 'inventories')) {
            $this->validateJWTForInventoryController($event, $accessToken, $params);
        }
    }

    private function validateJWTForItemController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        $id = $params['id'];

        $item = $this->cache->get('item_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->itemRepository->findOneBy(['id' => $id]);
        });

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if (!$this->securityService->isClientAllowedForAdjustmentOnItem($accessToken, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }

    private function validateJWTForInventoryController(
        RequestEvent $event,
        array $accessToken,
        array $params
    ): void
    {
        if (count($params) === 0 && $this->securityService->isClientAdmin($accessToken)) {
            return;
        }

        if ((count($params) === 0) && !$this->securityService->isClientAdmin($accessToken)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));

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

        $item = array_key_exists('itemId', $params) ? $this->cache->get('item_' . $params['itemId'], function (ItemInterface $item) use ($params) {
            $item->expiresAfter(86400);

            return $this->itemRepository->findOneBy(['id' => $params['itemId']]);
        }) : null;

        if (array_key_exists('itemId', $params) && is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if (($user->isPrivate() && !$this->securityService->isClientAllowedForAdjustmentOnUserInventory($accessToken, $user, $item) || !$user->isPrivate() && !$event->getRequest()->isMethod('GET') && !$this->securityService->isClientAllowedForAdjustmentOnUserInventory($accessToken, $user, $item))) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9],
        ];
    }


}
