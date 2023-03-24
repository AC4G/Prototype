<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIItemListenerService
{
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly UserRepository $userRepository,
        private readonly ItemRepository $itemRepository,
        private readonly CustomResponse $customResponse
    )
    {
    }

    public function validateJWTAndParameterForItemController(
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
            $user = $this->userRepository->getUserByUuidFromCache($params['uuid']);

            if (is_null($user)) {
                $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'User doesn\'t exists!', 404));
            }

            return;
        }

        if (!array_key_exists('id', $params)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item id not passed in URI!', 400));

            return;
        }

        $id = $params['id'];

        $item = $this->itemRepository->getItemFromCacheInJsonFormatById($id);

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if ($event->getRequest()->isMethod('GET')) {
            return;
        }

        if (is_string($item)) {
            $item = json_decode($item, true);
        }

        if (!$this->securityService->hasClientPermissionForItemAction($accessToken, $item, $event->getRequest())) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }


}
