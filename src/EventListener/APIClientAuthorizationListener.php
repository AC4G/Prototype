<?php declare(strict_types=1);

namespace App\EventListener;

use App\Repository\ItemRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class APIClientAuthorizationListener implements EventSubscriberInterface
{
    private array $securedRoutes = [
        'api_items_by_identifier' => 'PATCH',
        'api_item_by_id_process_parameters' => 'DELETE',
    ];

    public function __construct(
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly ItemRepository $itemRepository,
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

        if (!array_key_exists($route, $this->securedRoutes) || $this->securedRoutes[$route] !== $method) {
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

    private function validateJWTForItemController(
        RequestEvent $event,
        string $jwt,
        array $params
    ): void
    {
        $id = array_key_exists('property', $params) ? (int)$params['property'] : $params['id'];

        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if (!$this->securityService->isClientAllowedForAdjustmentOnItem($jwt, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Rejected!', 403));

            return;
        }

        $this->cache->get('item_' . $item->getId(), function () use ($item) {
            return $item;
        });
    }

    private function validateJWTForInventoryController(
        RequestEvent $event,
        string $jwt,
        array $params
    ): void
    {
        //
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9],
        ];
    }


}
