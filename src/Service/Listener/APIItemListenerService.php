<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Repository\ItemRepository;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIItemListenerService
{
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly ItemRepository $itemRepository,
        private readonly CustomResponse $customResponse
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

        $item = $this->itemRepository->getItemFromCacheInJsonFormatById($id);

        if (is_null($item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Item not found', 404));

            return;
        }

        if ($event->getRequest()->isMethod('GET')) {
            if (str_contains($event->getRequest()->attributes->get('_route'), 'parameter')) {
                $this->itemRepository->getItemParameterFromCacheById($id);

                return;
            }

            $this->itemRepository->getItemFromCacheInJsonFormatById($id);

            return;
        }

        if (is_string($item)) {
            $item = json_decode($item, true);
        }

        if (!$this->securityService->hasClientPermissionForAdjustmentOnItem($accessToken, $item)) {
            $event->setResponse($this->customResponse->errorResponse($event->getRequest(), 'Permission denied!', 403));
        }
    }


}
