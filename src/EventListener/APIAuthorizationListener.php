<?php declare(strict_types=1);

namespace App\EventListener;

use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use App\Service\Listener\APIAuthorizationListenerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class APIAuthorizationListener implements EventSubscriberInterface
{

    public function __construct(
        private readonly APIAuthorizationListenerService $authorizationListenerService,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse
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

        if (str_starts_with($route, 'api_item')) {
            $this->authorizationListenerService->validateJWTForItemController($event, $accessToken, $params);

            return;
        }

        if (str_starts_with($route, 'api_inventory') || str_starts_with($route, 'api_inventories')) {
            $this->authorizationListenerService->validateJWTForInventoryController($event, $accessToken, $params);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9],
        ];
    }


}