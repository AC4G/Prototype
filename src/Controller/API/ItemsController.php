<?php declare(strict_types=1);

namespace App\Controller\API;


use App\Entity\Item;
use App\Service\API\Items\ItemsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemsController extends AbstractController
{
    public function __construct(
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

        if (count($items) > 0) {
            return new JsonResponse(
                $this->itemsService->prepareData($items)
            );
        }

        $data = [
            'response' => [
                'status' => 200,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => 'Not one item stored. Maybe next time..'
            ]
        ];

        return new JsonResponse(
            $data
        );
    }

    /**
     * @Route("/api/items/{property}", name="api_items_by_identifier", methods={"GET", "PUT"})
     */
    public function processItem(
        Request $request,
        string $property
    ): ?Response
    {
        //TODO: PUT -> only with

        $item = $this->itemsService->getItemDependentOnProperty($property);

        if ($request->isMethod('GET')) {
            if (!$item instanceof Item && !is_array($item)) {
                $data = [
                    'error' => [
                        'status' => 404,
                        'source' => [
                          'pointer' => $request->getUri()
                        ],
                        'message' => is_numeric($property) ? 'Item not found' : 'User not exist or has not created an Item yet!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    404
                );
            }

            return new JsonResponse(
                $this->itemsService->prepareData($item)
            );
        }

        if (!is_numeric($property)) {
            $data = [
                'error' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'For updating item the property must by ID not the NAME'
                ]
            ];

            return new JsonResponse(
                $data,
                406
            );
        }

        $json = $request->getContent();

        $newParameter = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'No valid JSON. Please do it right!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        if (!is_numeric($property)) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Property must be id for the PUT method!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        $item = $this->itemsService->updateItem($property, $newParameter);

        if (!$item instanceof Item) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Item not found'
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        return new JsonResponse(
            $this->itemsService->prepareData($item)[0],
            202
        );
    }


}
