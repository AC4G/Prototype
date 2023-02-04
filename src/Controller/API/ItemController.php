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
        $accessToken = $request->getSession()->get('accessToken');
        $request->getSession()->remove('accessToken');

        if ($request->isMethod('PATCH')) {
            $newParameter = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
            }

            $item = $this->itemRepository->findOneBy(['id' => $id]);

            $this->itemsService->updateItem($item, $newParameter);
            $this->cache->delete('item_' . $id . '_project_' . $accessToken['project']['id']);

            return $this->customResponse->notificationResponse($request, 'Parameter successfully added or updated!', 202);
        }

        $item = $this->cache->getItem('item_' . $id . '_project_' . $accessToken['project']['id'])->get();

        return new Response(
            $item,
            200,
            [
                'Content-Type' => 'application/json'
            ]
        );
    }

    /**
     * @Route("/api/items/user/{userId}", name="api_items_by_nickname", methods={"GET"}, requirements={"userId" = "\d+"})
     */
    public function getItemsByNickname(
        Request $request,
        string $userId
    ): Response
    {
        $user = $this->userRepository->findOneBy(['id' => $userId]);

        if (is_null($user)) {
            return $this->customResponse->errorResponse($request, 'User doesn\'t exists!', 404);
        }

        $items = $this->itemRepository->findBy(['user' => $user]);

        if (count($items) === 0) {
            return $this->customResponse->errorResponse($request, 'User hasn\'t created an item yet!', 400);
        }

        $format = $this->itemsService->getFormat($request);

        return new JsonResponse(
            $this->itemsService->prepareData($items, $format)
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
        $accessToken = $request->getSession()->get('accessToken');
        $request->getSession()->remove('accessToken');

        if ($request->isMethod('GET')) {
            $item = $this->cache->getItem('item_' . $id . '_project_' . $accessToken['project']['id'])->get();

            $itemParameter = json_decode($item->getParameter(), true);

            return new JsonResponse(
                $itemParameter
            );
        }

        $item = $this->itemRepository->findOneBy(['id' => $id]);
        $itemParameter = json_decode($item->getParameter(), true);

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

        $this->itemsService->deleteParameter($parameters, $item);
        $this->cache->delete('item_' . $id . '_project_' . $accessToken['project']['id']);

        return $this->customResponse->notificationResponse($request, sprintf('Parameter successfully removed from item with id: %s', $id));
    }


}
