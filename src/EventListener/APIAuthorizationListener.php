<?php declare(strict_types=1);

namespace App\EventListener;

use DateTime;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use App\Service\Listener\APIUserListenerService;
use App\Service\Listener\APIItemListenerService;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use App\Service\Listener\APIInventoryListenerService;
use App\Service\Listener\APIPublicKeyListenerService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class APIAuthorizationListener implements EventSubscriberInterface
{

    public function __construct(
        private readonly APIInventoryListenerService $apiInventoryListenerService,
        private readonly APIPublicKeyListenerService $apiPublicKeyListenerService,
        private readonly APIItemListenerService $apiItemListenerService,
        private readonly APIUserListenerService $apiUserListenerService,
        private readonly RateLimiterFactory $apiFreePerMinuteLimiter,
        private readonly RateLimiterFactory $apiFreeLimiter,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
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

        $apiLimiter = $this->apiFreeLimiter->create('project_' . $accessToken['project']['id']);
        $apiLimiterPerMinute = $this->apiFreePerMinuteLimiter->create('project_per_minute_' . $accessToken['project']['id']);

        if (!$apiLimiter->consume()->isAccepted()) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Used limit per day', 429));

            return;
        }

        if (!$apiLimiterPerMinute->consume()->isAccepted()) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Used limit per minute', 429));

            return;
        }

        if ((new DateTime($accessToken['expireDate']['date']) < new DateTime())) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Access token is expired!', 400));

            return;
        }

        $event->getRequest()->getSession()->set('scopes', $accessToken['scopes']);

        if (str_starts_with($route, 'api_item')) {
            $this->apiItemListenerService->validateJWTForItemController($event, $accessToken, $params);

            return;
        }

        if (str_starts_with($route, 'api_inventory') || str_starts_with($route, 'api_inventories')) {
            $this->apiInventoryListenerService->validateJWTForInventoryController($event, $accessToken, $params);

            return;
        }

        if (str_starts_with($route, 'api_user')) {
            $this->apiUserListenerService->validateJWTForUserController($event, $accessToken, $params);

            return;
        }

        if (str_starts_with($route, 'api_public_key')) {
            $this->apiPublicKeyListenerService->validateJWTForPublicKeyController($event, $accessToken, $params);
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 9],
        ];
    }


}
