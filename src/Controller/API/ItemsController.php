<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\API\Items\ItemsService;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemsController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ItemRepository $itemRepository,
        private CustomResponse $customResponse,
        private ItemsService $itemsService
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
            return $this->customResponse->errorResponse($request, 'Not one item stored. Maybe next time..', 404);
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

        //TODO: PATCH -> only with authentication

        if ($request->isMethod('GET')) {
            if (!is_numeric($property)) {
                $user = $this->userRepository->findOneBy(['nickname' => $property]);

                if (is_null($user)) {
                    return $this->customResponse->errorResponse($request, 'User don\'t exists!', 404);
                }

                $item = $this->itemRepository->findBy(['user' => $user]);
            }

            if (is_numeric($property)) {
                $item = $this->itemRepository->findOneBy(['id' => (int)$property]);
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

        $newParameter = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (!is_numeric($property)) {
            return $this->customResponse->errorResponse($request, 'For the PATCH method the property must be numeric!');
        }

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request, 'Item not found', 404);
        }

        $this->itemsService->updateItem($item, $newParameter);

        return $this->customResponse->notificationResponse($request, 'Parameter successfully added or updated!', 202);
    }

    /**
     * @Route("/api/items/{id}/parameters", name="api_item_by_id_process_parameters", methods={"DELETE", "GET"}, requirements={"id" = "\d+"})
     */
    public function processParameter(
        Request $request,
        int $id
    ): Response
    {
        //TODO: DELETE only with authentication
        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
            return $this->customResponse->errorResponse($request, 'Item not found', 404);
        }

        $itemParameter = json_decode($item->getParameter(), true);

        if (count($itemParameter) === 0) {
            return $this->customResponse->notificationResponse($request, sprintf('Item with id %s don\'t has parameter yet!', $id));
        }

        if ($request->isMethod('GET')) {
            return new JsonResponse(
                $itemParameter
            );
        }

        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (count($parameters) === 0) {
            return  $this->customResponse->errorResponse($request, 'Not even passed a parameter for delete. Nothing changed!', 406);
        }

        $this->itemsService->deleteParameter($parameters, $item);

        return $this->customResponse->notificationResponse($request, sprintf('Parameter successfully removed from item %s', $id));
    }


}
