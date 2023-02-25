<?php declare(strict_types=1);

namespace App\Service\Listener;

use App\Entity\Item;
use App\Serializer\ItemNormalizer;
use App\Repository\ItemRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class APIItemListenerService
{
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly ItemRepository $itemRepository,
        private readonly ItemNormalizer $itemNormalizer,
        private readonly CustomResponse $customResponse,
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
            $itemCache->set(json_encode($this->itemNormalizer->normalize($item, null, 'public')));
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


}
