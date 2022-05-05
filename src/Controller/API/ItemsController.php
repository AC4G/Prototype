<?php declare(strict_types=1);

namespace App\Controller\API;


use App\Entity\Item;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Service\API\Items\ItemsService;
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
        private ItemsService $itemsService,
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
     * @Route("/api/items/{property}", name="api_items_by_identifier", methods={"GET", "PATCH"})
     */
    public function processItem(
        Request $request,
        string $property
    ): ?Response
    {
        //TODO: PUT -> only with oauth

        if ($request->isMethod('GET')) {
            $item = '';

            if (!is_numeric($property)) {
                $user = $this->userRepository->findOneBy(['nickname' => $property]);

                if (is_null($user)) {
                    $data = [
                        'error' => [
                            'status' => 404,
                            'source' => [
                                'pointer' => $request->getUri()
                            ],
                            'message' => 'User not exist!'
                        ]
                    ];

                    return new JsonResponse(
                        $data,
                        404
                    );
                }

                $item = $this->itemRepository->findBy(['user' => $user]);
            }

            if (is_numeric($property)) {
                $item = $this->itemRepository->findOneBy(['id' => (int)$property]);
            }

            if (is_null($item)) {
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

            if (is_array($item)) {
                if (!count($item) > 0) {
                    $data = [
                        'error' => [
                            'status' => 400,
                            'source' => [
                                'pointer' => $request->getUri()
                            ],
                            'message' => 'User hasn\'t an item yet!'
                        ]
                    ];

                    return new JsonResponse(
                        $data,
                        400
                    );
                }
            }

            return new JsonResponse(
                $this->itemsService->prepareData($item)
            );
        }

        $newParameter = json_decode($request->getContent(), true);

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
                    'message' => 'For the PUT method the property must be numeric!'
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

    /**
     * @Route("/api/items/{id}/parameters", name="api_item_by_id_remove_parameters", methods={"DELETE"}, requirements={"id" = "\d+"})
     */
    public function deleteParameter(
        Request $request,
        int $id
    ): Response
    {
        //TODO: only with oauth

        $item = $this->itemRepository->findOneBy(['id' => $id]);

        if (is_null($item)) {
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

        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = [
                'error' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Invalid json!'
                ]
            ];

            return new JsonResponse(
                $data,
                406
            );
        }

        if (!count($parameters) > 0) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Not even passed a parameter for delete. Nothing changed!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        $this->itemsService->deleteParameter($parameters, $item);

        $data = [
            'notification' => [
                'status' => 200,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => sprintf('Parameter successfully removed from item %s', $id)
            ]
        ];

        return new JsonResponse(
            $data,
            200
        );
    }


}
