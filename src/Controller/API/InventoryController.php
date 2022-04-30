<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\DataService;
use App\Repository\InventoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\API\Inventories\InventoriesService;

class InventoryController
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private InventoriesService $inventoriesService,
        private DataService $dataService
    )
    {
    }

    /**
     * @Route("/api/inventories", name="api_inventories", methods={"GET"})
     */
    public function showInventories(
        Request $request
    ): Response
    {
        //TODO:only for admins ->authentication via jwt

        $inventory = $this->inventoryRepository->findAll();

        if (count($inventory) > 0) {
            $data = $this->dataService
                ->convertObjectToArray($inventory)
                ->rebuildPropertyArray('user', [
                    'id',
                    'nickname'
                ])
                ->rebuildPropertyArray('item', [
                    'id',
                    'name',
                    'gameName'
                ])
                ->convertPropertiesToJson([
                    'parameter'
                ])
                ->removeProperties([
                    'id'
                ])
                ->getArray()
            ;

            return new JsonResponse(
                $data
            );
        }

        $data = [
            'status' => 200,
            'source' => [
                'pointer' => $request->getUri()
            ],
            'message' => 'No inventories here, maybe next time...'
        ];

        return new JsonResponse(
            $data
        );
    }

    /**
     * @Route("/api/inventories/{property}", name="api_inventories_by_property", methods={"GET", "POST", "PUT"})
     */
    public function processInventory(
        Request $request,
        string $property
    ): Response
    {
        //TODO: if user is private, than access only with jwt oauth2.0 and post put only with oauth2.0

        if ($request->isMethod('GET')) {
            $inventory = $this->inventoriesService->getInventoryByProperty($property);

            if (is_null($inventory) || count($inventory) === 0) {
                $data = [
                    'error' => [
                        'status' => 404,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'messages' => is_null($inventory) ? 'User not exists' : 'User has not an item in inventory yet!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    404
                );
            }

            $data = $this->dataService
                ->convertObjectToArray($inventory)
                ->rebuildPropertyArray('user', [
                    'id',
                    'nickname'
                ])
                ->rebuildPropertyArray('item', [
                    'id',
                    'name',
                    'gameName'
                ])
                ->convertPropertiesToJson([
                    'parameter'
                ])
                ->removeProperties([
                    'id'
                ])
                ->getArray()
            ;

            return new JsonResponse(
                $data
            );
        }

        $json = $request->getContent();
        $parameter = json_decode($json, true);

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

        if ($request->isMethod('PUT')) {
            $this->inventoriesService->updateInventory($parameter, $property);

            if ($this->inventoriesService->hasMessages()) {
                $messages = $this->inventoriesService->getMessages();

                $data = [
                    'error' => [
                        'status' => array_key_exists('user', $messages) ? 404 : 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'messages' => $messages
                    ]
                ];

                return new JsonResponse(
                    $data,
                    array_key_exists('user', $messages) ? 404 : 406
                );
            }

            $data = [
                'notification' => [
                    'status' => 200,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Inventory updated'
                ]
            ];

            return new JsonResponse(
                $data
            );
        }

        $this->inventoriesService->createEntryInInventory($parameter, $property);

        if ($this->inventoriesService->hasMessages()) {
            $messages = $this->inventoriesService->getMessages();

            $data = [
                'error' => [
                    'status' => (array_key_exists('user', $messages) || array_key_exists('item', $messages)) ? 404 : 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'messages' => $messages
                ]
            ];

            return new JsonResponse(
                $data,
                (array_key_exists('user', $messages) || array_key_exists('item', $messages)) ? 404 : 406
            );
        }

        $data = [
            'notification' => [
                'status' => 201,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => 'Item successfully added to inventory'
            ]
        ];

        return new JsonResponse(
            $data,
            201
        );
    }


}