<?php declare(strict_types=1);

namespace App\Controller\API;


use App\Entity\Item;
use App\Service\DataService;
use App\Service\API\Items\ItemsService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemsController extends AbstractController
{
    public function __construct(
        private ItemsService $itemsService,
        private DataService $dataService
    )
    {
    }

    /**
     * @Route("/api/items", name="api_items", methods={"GET"})
     */
    public function showItems(): Response
    {
        $data = $this->dataService
            ->convertObjectToArray($this->itemsService->getItems())
            ->rebuildPropertyArray('user', [
                'nickname',
            ])
            ->removeProperties([
                'path',
            ])
            ->convertPropertiesToJson([
                'parameter',
            ])
            ->getArray()
        ;

        return new JsonResponse(
            $data
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
        $item = $this->itemsService->getItemDependentOnProperty($property);

        if ($request->isMethod('GET')) {
            if (!$item instanceof Item && !is_array($item)) {
                $data = [
                    'errors' => [
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

            $processedItem = $this->dataService
                ->convertObjectToArray($item)
                ->rebuildPropertyArray('user', [
                    'nickname',
                ])
                ->convertPropertiesToJson([
                    'parameter',
                ])->removeProperties([
                    'path',
                ])
                ->getArray()
            ;

            return new JsonResponse(
                $processedItem
            );
        }

        if (!is_numeric($property)) {
            $data = [
                'errors' => [
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
                'errors' => [
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

        $item = $this->itemsService->updateItem($property, $newParameter);


        if (!$item instanceof Item) {
            $data = [
                'errors' => [
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

        $processedItem = $this->dataService
            ->convertObjectToArray($item)
            ->rebuildPropertyArray('user', [
                'nickname',
            ])
            ->convertPropertiesToJson([
                'parameter',
            ])->removeProperties([
                'path',
            ])
            ->getArray()[0]
        ;

        return new JsonResponse(
            $processedItem,
            202
        );
    }


}
