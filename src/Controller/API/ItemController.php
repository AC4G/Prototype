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

    #[Route('/api/items', name: 'api_items', methods: [Request::METHOD_GET])]
    public function showItems(
        Request $request
    ): Response
    {
        $items = $this->itemRepository->findAll();

        if (count($items) === 0) {
            return $this->customResponse->errorResponse($request, 'Not a single item created! Maybe next time..', 404);
        }

        return new JsonResponse(
            $this->itemsService->prepareData($items, null, 'public')
        );
    }

    #[Route('/api/items/{id}', name: 'api_item_by_id_get', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getItemById(
        int $id
    ): Response
    {
        $item = $this->itemRepository->getItemFromCacheInJsonFormatById($id);

        return new Response(
            $item,
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    #[Route('/api/items/{id}', name: 'api_item_by_id_patch', requirements: ['id' => '\d+'], methods: [Request::METHOD_PATCH])]
    public function patchItemById(
        Request $request,
        int $id
    ): Response
    {
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

    #[Route('/api/items/user/{uuid}', name: 'api_items_by_uuid', methods: [Request::METHOD_GET])]
    public function getItemsByUuid(
        Request $request,
        string $uuid
    ): Response
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        if (is_null($user)) {
            return $this->customResponse->errorResponse($request, 'User doesn\'t exists!', 404);
        }

        $items = $this->itemRepository->getItemsFromCacheByUser($user);

        if (count($items) === 0) {
            return $this->customResponse->errorResponse($request, 'User hasn\'t created an item yet!', 400);
        }

        return new JsonResponse(
            $this->itemsService->prepareData($items, null, 'public')
        );
    }

    #[Route('/api/items/{id}/parameter', name: 'api_item_by_id_process_parameter_get', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET])]
    public function getItemParameterById(
        int $id
    ): Response
    {
        $parameter = $this->itemRepository->getItemParameterFromCacheById($id);

        return new Response(
            $parameter,
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    #[Route('/api/items/{id}/parameter', name: 'api_item_by_id_process_parameter_delete', requirements: ['id' => '\d+'], methods: [Request::METHOD_DELETE])]
    public function deleteItemParameterById(
        Request $request,
        int $id
    ): Response
    {
        $itemParameter = json_decode($this->itemRepository->getItemParameterFromCacheById($id), true);

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
