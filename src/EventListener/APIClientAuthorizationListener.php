<?php declare(strict_types=1);

namespace App\EventListener;

use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class APIClientAuthorizationListener implements EventSubscriberInterface
{
    private array $securedRoutes = [
        'api_item_by_id' => 'PATCH',
        'api_item_by_id_process_parameters' => 'DELETE',
        'api_inventories' => 'GET',
        'api_inventory_by_userId' => 'GET',
        'api_inventory_by_userId_and_itemId' => [
            'GET',
            'POST',
            'PATCH',
            'DELETE',
        ],
        'api_inventory_by_userId_and_itemId_parameter' => [
            'GET',
            'DELETE',
        ],
    ];

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

        $method = $request->getMethod();

        if (!$this->isRouteSecured($route, $method)) {
            return;
        }

        $jwt = $request->headers->get('Authorization');
        $params = $request->attributes->get('_route_params');

        if (is_null($jwt)) {
            $event->setResponse($this->customResponse->errorResponse($request, 'No JWT passed!', 403));

            return;
        }

        if (str_contains($route, 'item')) {
            $this->validateJWTForItemController($event, $jwt, $params);

            return;
        }

        if (str_contains($route, 'inventory') || str_contains($route, 'inventories')) {
            $this->validateJWTForInventoryController($event, $jwt, $params);
        }
    }

    private function isRouteSecured(
        string $route,
        string $method
    ): bool
    {
        return array_key_exists($route, $this->securedRoutes) && ($this->securedRoutes[$route] === $method || (is_array($this->securedRoutes[$route]) && in_array($method, $this->securedRoutes[$route])));
    }

    private function validateJWTForItemController(
        RequestEvent $event,
        string $jwt,
        array $params
    ): void
    {
        $id = $params['id'];

        $item = $this->cache->get('item_' . $id, function () use ($id) {
            return $this->itemRepository->findOneBy(['id' => $id]);
        });

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if (!$this->securityService->isClientAllowedForAdjustmentOnItem($jwt, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Rejected!', 403));
        }
    }

    private function validateJWTForInventoryController(
        RequestEvent $event,
        string $jwt,
        array $params
    ): void
    {
        if (count($params) === 0 && $this->securityService->isClientAdmin($jwt)) {
            return;
        }

        if ((count($params) === 0) && !$this->securityService->isClientAdmin($jwt)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Rejected!', 403));

            return;
        }

        $userId = $params['userId'];


        $user = $this->cache->get('user_'. $userId, function () use ($userId) {
            return $this->userRepository->findOneBy(['id' => $userId]);
        });

        if (is_null($user)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), sprintf('User with id %s doesn\'t exists!', $userId), 404));

            return;
        }
        if (($user->isPrivate() && !$this->securityService->isClientAllowedForAdjustmentOnUserContent($jwt, $user) || !$user->isPrivate() && !$event->getRequest()->isMethod('GET') && !$this->securityService->isClientAllowedForAdjustmentOnUserContent($jwt, $user))) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Rejected!', 403));
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9],
        ];
    }


}
