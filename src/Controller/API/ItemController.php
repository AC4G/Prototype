<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\API\Item\ItemService;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly ItemRepository $itemRepository,
        private readonly CustomResponse $customResponse,
        private readonly ItemService $itemsService,
        private readonly CacheInterface $cache
    )
    {
    }

    /**
     * @Route("/api/items", name="api_items", methods={"GET"})
     */
    public function showItems(
        Request $request
    ): Response
    {
        $items = $this->itemsService->getItems();

        if (count($items) === 0) {
            return $this->customResponse->errorResponse($request, 'No one item stored. Maybe next time..', 404);
        }

        return new JsonResponse(
            $this->itemsService->prepareData($items)
        );
    }

    /**
     * @Route("/api/items/{id}", name="api_item_by_id", methods={"GET", "PATCH"}, requirements={"id" = "\d+"})
     */
    public function processItemById(
        Request $request,
        int $id
    ): ?Response
    {
        if ($request->isMethod('PATCH')) {
            $item = $this->cache->get('item_' . $id, function () {
                return null;
            });

            if (is_null($item)) {
                return  $this->customResponse->errorResponse($request, 'Internal error, retry again!', 500);
            }

            $newParameter = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
            }

            $this->itemsService->updateItem($item, $newParameter);

            return $this->customResponse->notificationResponse($request, 'Parameter successfully added or updated!', 202);
        }

        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request,'Item not found', 404);
        }

        return new JsonResponse(
            $this->itemsService->prepareData($item)
        );
    }

    /**
     * @Route("/api/items/user/{nickname}", name="api_items_by_nickname", methods={"GET"})
     */
    public function getItemsByNickname(
        Request $request,
        string $nickname
    ): Response
    {
        $user = $this->userRepository->findOneBy(['nickname' => $nickname]);

        if (is_null($user)) {
            return $this->customResponse->errorResponse($request, 'User doesn\'t exists!', 404);
        }

        $items = $this->itemRepository->findBy(['user' => $user]);

        if (count($items) === 0) {
            return $this->customResponse->errorResponse($request, 'User hasn\'t created an item yet!', 400);
        }

        return new JsonResponse(
            $this->itemsService->prepareData($items)
        );
    }

    /**
     * @Route("/api/items/{id}/parameters", name="api_item_by_id_process_parameters", methods={"DELETE", "GET"}, requirements={"id" = "\d+"})
     */
    public function processParameter(
        Request $request,
        int $id
    ): Response
    {
        if ($request->isMethod('DELETE')) {
            $item = $this->cache->get('item_' . $id, function () {});

            if (is_null($item)) {
                return  $this->customResponse->errorResponse($request, 'Internal error, retry again!', 500);
            }

            $parameters = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
            }

            if (count($parameters) === 0) {
                return  $this->customResponse->errorResponse($request, 'Not even passed a parameter for deletion. Nothing changed!', 406);
            }

            $this->itemsService->deleteParameter($parameters, $item);

            return $this->customResponse->notificationResponse($request, sprintf('Parameter successfully removed from item with id: %s', $id));
        }

        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request, 'Item not found', 404);
        }

        $itemParameter = json_decode($item->getParameter(), true);

        if (count($itemParameter) === 0) {
            return $this->customResponse->notificationResponse($request, sprintf('Item with id %s doesn\'t has parameter yet!', $id));
        }

        return new JsonResponse(
            $itemParameter
        );
    }


}
