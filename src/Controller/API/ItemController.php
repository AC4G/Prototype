<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\API\Item\ItemService;
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
        private readonly ItemService $itemsService
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
     * @Route("/api/items/{property}", name="api_items_by_identifier", methods={"GET", "PATCH"})
     */
    public function processItem(
        Request $request,
        string $property
    ): ?Response
    {
        $item = '';

        if (is_numeric($property)) {
            $item = $this->itemRepository->findOneBy(['id' => (int)$property]);
        }

        if ($request->isMethod('PATCH')) {
            $newParameter = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
            }

            $this->itemsService->updateItem($item, $newParameter);

            return $this->customResponse->notificationResponse($request, 'Parameter successfully added or updated!', 202);
        }

        if (!is_numeric($property)) {
            $user = $this->userRepository->findOneBy(['nickname' => $property]);

            if (is_null($user)) {
                return $this->customResponse->errorResponse($request, 'User doesn\'t exists!', 404);
            }

            $item = $this->itemRepository->findBy(['user' => $user]);
        }

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request,'Item not found', 404);
        }

        if (is_array($item)) {
            if (count($item) === 0) {
                return $this->customResponse->errorResponse($request, 'User hasn\'t created an item yet!', 400);
            }
        }

        return new JsonResponse(
            $this->itemsService->prepareData($item)
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
        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if ($request->isMethod('DELETE')) {
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
