<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
use App\Repository\UserRepository;
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
        private UserRepository $userRepository,
        private ItemRepository $itemRepository
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
            return new JsonResponse(
                $this->inventoriesService->prepareInventories($inventory)
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

            return new JsonResponse(
                $this->inventoriesService->prepareInventories($inventory)
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

        //PUT POST starts from here

        $messages = [];

        if (!array_key_exists('itemId', $parameter)) {
            $messages['itemId'] = 'JSON not contain itemId from item';
        }

        if (!array_key_exists('itemId', $parameter) && !array_key_exists('parameter', $parameter)) {
            $messages['amount'] = 'JSON not contain amount of items and parameter. On of them are necessary!';
        }

        if (count($messages) > 0) {
            $data = [
                'error' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'messages' => $messages
                ]
            ];

            return new JsonResponse(
                $data,
                406
            );
        }

        $user = $this->userRepository->findOneBy((is_numeric($property) ? ['id' => (int)$property] : ['nickname' => $property]));

        if (is_null($user)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => is_numeric($property) ? sprintf('User with id %s don\'t exists!', $property) : sprintf('User %s don\'t exists!', $property)
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        $item = $this->itemRepository->findOneBy(['id' => (int)$parameter['itemId']]);

        if (is_null($item)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => is_numeric($parameter['itemId']) ? sprintf('Item with id %s don\'t exists', $parameter['itemId']) : sprintf('Item id must be numeric and not like that %s', $parameter['itemId'])
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);

        if (is_null($inventory)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  'User does not has this item in inventory. Please use POST method to add item!'
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        if ($request->isMethod('PUT')) {

            $this->inventoriesService->updateInventory($parameter, $inventory);

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

        $this->inventoriesService->createEntryInInventory($parameter, $user, $item);

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