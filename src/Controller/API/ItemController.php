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
            return $this->customResponse->errorResponse($request, 'Not a single item created! Maybe next time..', 404);
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
            $newParameter = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
            }

            $data = $this->itemRepository->getNameAndParameter($id);

            $this->itemsService->updateItem($id, $data, $newParameter);
            $this->cache->delete('item_' . $id);
            $this->cache->delete('item_' . $id . '_parameter');

            return $this->customResponse->notificationResponse($request, 'Parameter successfully added or updated!', 202);
        }

        $item = $this->itemsService->getItemFromCacheById($id);

        return new Response(
            $item,
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    /**
     * @Route("/api/items/user/{uuid}", name="api_items_by_uuid", methods={"GET"})
     */
    public function getItemsByNickname(
        Request $request,
        string $uuid
    ): Response
    {
        $user = $this->userRepository->findOneBy(['uuid' => $uuid]);

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
     * @Route("/api/items/{id}/parameter", name="api_item_by_id_process_parameter", methods={"DELETE", "GET"}, requirements={"id" = "\d+"})
     */
    public function processParameter(
        Request $request,
        int $id
    ): Response
    {
        if ($request->isMethod('GET')) {
            $parameter = $this->itemsService->getItemParameterFromCacheById($id);

            return new Response(
                $parameter,
                200,
                [
                    'Content-Type' => 'application/json'
                ]
            );
        }

        $itemParameter = json_decode($this->itemsService->getItemParameterFromCacheById($id), true);

        $parameters = json_decode($request->getContent(), true);

        if (count($itemParameter) === 0) {
            return $this->customResponse->notificationResponse($request, sprintf('Item with id %s doesn\'t has parameter yet. Nothing deleted!', $id));
        }

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (count($parameters) === 0) {
            return  $this->customResponse->errorResponse($request, 'Not even passed a parameter for deletion. Nothing changed!', 406);
        }

        $this->itemsService->deleteParameter($id, $itemParameter, $parameters);
        $this->cache->delete('item_' . $id);
        $this->cache->delete('item_' . $id . '_parameter');

        return $this->customResponse->notificationResponse($request, sprintf('Parameter successfully removed from item with id: %s', $id));
    }


}
