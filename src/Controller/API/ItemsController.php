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
        $processedData = $this->dataService
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
            ->getArray();

        return new JsonResponse($processedData);
    }

    /**
     * @Route("/api/items/{property}", name="api_items_by_identifier", methods={"GET", "PATCH"})
     */
    public function item(
        Request $request,
        string $property
    ): ?Response
    {
        if ($request->isMethod('GET')) {
            $item = $this->itemsService->getItemDependentOnProperty($property);

            if (!$item instanceof Item && !is_array($item)) {
                $data = [
                    'errors' => [
                        'status' => 406,
                        'source' => [
                          'pointer' => $request->getUri()
                        ],
                        'message' => is_numeric($property) ? 'Item not found' : 'User not exist or hasn\'t created an Item yet!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406,
                    [
                        'application/json'
                    ]
                );
            }

            $processedItem = $this->dataService
                ->convertObjectToArray(
                    is_array($item) ? $item : [$item]
                )
                ->rebuildPropertyArray('user', [
                    'nickname',
                ])
                ->convertPropertiesToJson([
                    'parameter',
                ])->removeProperties([
                    'path',
                ])
                ->getArray();

            return (new JsonResponse(
                $processedItem,
                200,
                [
                    'application/json'
                ]
            ));
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
                406,
                [
                    'application/json'
                ]
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
                400,
                [
                    'application/json'
                ]
            );
        }

        $item = $this->itemsService->updateItem($property, $newParameter);


        if (!$item instanceof Item) {
            $data = [
                'errors' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Item not found'
                ]
            ];

            return new JsonResponse(
                $data,
                406,
                [
                    'application/json'
                ]
            );
        }

        $processedItem = $this->dataService
            ->convertObjectToArray(
                [$item]
            )
            ->rebuildPropertyArray('user', [
                'nickname',
            ])
            ->convertPropertiesToJson([
                'parameter',
            ])->removeProperties([
                'path',
            ])
            ->getArray();

        return new JsonResponse(
            $processedItem,
            202,
            [
                'application/json'
            ]
        );
    }
}
