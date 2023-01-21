<?php declare(strict_types=1);

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIClientAuthorizationListener implements EventSubscriberInterface
{
    public function __construct(

    )
    {
    }

    public function onKernelRequest(
        RequestEvent $event
    )
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if (strpos($route, 'api_') === 0) {
            return;
        }

        $method = $request->getMethod();
        $securedRoutes = $this->getRoutesWithMethodsForSecuring();

        if (!array_key_exists($route, $securedRoutes) || $securedRoutes[$route] !== $method) {
            return;
        }

        $jwt = $request->headers->get('Authorization');
        $params = $request->attributes->get('_route_params');

        //add middleware for every controller

    }

    private function getRoutesWithMethodsForSecuring(): array
    {
        return [
            'api_items_by_identifier' => 'PATCH',
            'api_item_by_id_process_parameters' => 'DELETE',
        ];
    }

    private function validateJWTForItemsRoutes(

    )
    {

    }

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => ['onKernelRequest', 10],
        ];
    }


}
