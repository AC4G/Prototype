<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\DataService;
use App\Repository\UserRepository;
use App\Service\API\Chat\ChatService;
use App\Repository\ChatRoomRepository;
use App\Repository\ChatRoomMemberRepository;
use App\Repository\ChatRoomMessageRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ChatController extends AbstractController
{
    public function __construct(
        private ChatRoomMessageRepository $chatRoomMessageRepository,
        private ChatRoomMemberRepository $roomMemberRepository,
        private ChatRoomRepository $chatRoomRepository,
        private UserRepository $userRepository,
        private ChatService $chatService,
        private DataService $dataService
    )
    {
    }

    /**
     * @Route("/api/chat", name="api_chat", methods={"POST"})
     */
    public function chat(
        Request $request
    ): Response
    {
        //TODO: everything with jwt oauth2.0

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

        if (!array_key_exists('userId', $parameter) && !array_key_exists('type', $parameter)) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'userId and type aren\'t included in json!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }


        if (!array_key_exists('userId', $parameter) || !array_key_exists('type', $parameter)) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => (!array_key_exists('userId', $parameter)) ? 'userId isn\'t included in json!' : 'type isn\'t included in json!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        if (!is_numeric($parameter['userId'])) {
            $data = [
                'error' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'userId isn\'t numeric!'
                ]
            ];

            return new JsonResponse(
                $data,
                406
            );
        }

        $user = $this->userRepository->findOneBy(['id' => (int)$parameter['userId']]);

        if (is_null($user)) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => sprintf('User with id: %s not exists!', $parameter['userId'])
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        $room = $this->chatService->createRoom($parameter, $user);

        if (is_null($room)) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Something went wrong. Try it one more time!'
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        $convertedData = $this->dataService->convertObjectToArray($room);
        $data['room'] = $this->dataService->rebuildArrayToOneValue($convertedData, 'type', 'type');

        $roomMembers = $this->roomMemberRepository->findBy(['chatRoomId' => $room->getId()]);

        $convertedData = $this->dataService->convertObjectToArray($roomMembers);
        $data['member'] = $this->dataService->removeProperties($convertedData, [
            'id',
            'chatRoom'
        ]);
        $data['member'] = $this->dataService->rebuildPropertyArray($data['member'], 'user', [
            'id',
            'nickname'
        ]);

        return new JsonResponse($data);
    }

    /**
     * @Route("/api/chat/{id}", name="api_chat_by_id", methods={"GET", "PATCH", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatById(
        Request $request,
        int $id
    ): Response
    {
        //TODO: everything with jwt oauth2.0
        $room = $this->chatRoomRepository->findOneBy(['id' => $id]);

        if (is_null($room)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  sprintf('Chat room with id %s don\'t exists', $id)
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        if ($request->isMethod('GET')) {
            $convertedData = $this->dataService->convertObjectToArray($room);
            $processedData = $this->dataService->decodeJson($convertedData, 'settings');
            $processedData = $this->dataService->decodeJson($processedData, 'parameter');
            $data['room'] = $this->dataService->rebuildArrayToOneValue($processedData, 'type', 'type');

            $roomMembers = $this->roomMemberRepository->findBy(['chatRoomId' => $room->getId()]);

            $convertedData = $this->dataService->convertObjectToArray($roomMembers);

            $data['members'] = $this->dataService->removeProperties($convertedData, [
                'id',
                'chatRoom'
            ]);
            $data['members'] = $this->dataService->rebuildPropertyArray($data['members'], 'user', [
                'id',
                'nickname'
            ]);

            return new JsonResponse(
                $data
            );
        }

        if ($request->isMethod('DELETE')) {
            $this->chatService->deleteRoomAndDependencies($room);

            $data = [
                'notification' => [
                    'status' => 200,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => 'Room and dependencies were deleted!'
                ]
            ];

            return new JsonResponse(
                $data
            );
        }

        //TODO: PATCH -> request attached image -> image path; Response -> json: room with changes
        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = [
                'error' => [
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  'Invalid json!'
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
                    'status' => 406,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  'Content is empty. Nothing saved or updated!'
                ]
            ];

            return new JsonResponse(
                $data,
                406
            );
        }

        if (array_key_exists('add', $parameters)) {
            if (!is_countable($parameters['add'])) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' => 'add must be a list!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            foreach ($parameters['add'] as $member) {
                $user = $this->userRepository->findOneBy(['id' => $member]);

                if (is_null($user)) {
                    $data = [
                        'error' => [
                            'status' => 404,
                            'source' => [
                                'pointer' => $request->getUri()
                            ],
                            'message' =>  sprintf('User with id %s don\'t exists', $member)
                        ]
                    ];

                    return new JsonResponse(
                        $data,
                        404
                    );
                }

                if($this->chatService->addUserToRoom($user, $room) === false) {
                    $data = [
                        'error' => [
                            'status' => 400,
                            'source' => [
                                'pointer' => $request->getUri()
                            ],
                            'message' =>  sprintf('Chat room with id %s is private and has already 2 members', $id)
                        ]
                    ];

                    return new JsonResponse(
                        $data,
                        400
                    );
                }
            }
        }

        if (array_key_exists('settings', $parameters)) {
            if (!is_countable($parameters['settings'])) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  'settings must be a list!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            $this->chatService->addOrUpdateSettings($parameters['settings'], $room);
        }

        if (array_key_exists('parameters', $parameters)) {
            if (!is_countable($parameters['parameters'])) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  'parameters must be a list!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            $this->chatService->addOrUpdateParameter($parameters['parameters'], $room);
        }

        if (array_key_exists('name', $parameters)) {
            $this->chatService->addOrUpdateName($parameters['name'], $room);
        }

        $data = [
            'notification' => [
                'status' => 200,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => sprintf('Chat room with id %s successfully updated', $id)
            ]
        ];

        return new JsonResponse(
            $data
        );
    }

    /**
     * @Route("/api/chat/{id}/messages", name="api_chat_by_id_messages", methods={"GET", "POST", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatByIdMessages(
        Request $request,
        int $id
    ): Response
    {
        //TODO: everything with jwt oauth2.0

        $room = $this->chatRoomRepository->findOneBy(['id' => $id]);

        if (is_null($room)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  sprintf('Chat room with id %s don\'t exists', $id)
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        $messages = $this->chatRoomMessageRepository->findBy(['room' => $room]);

        if (!count($messages) > 0) {
            $data = [
                'error' => [
                    'status' => 400,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  sprintf('Chat room with id %s do not contain messages yet!', $id)
                ]
            ];

            return new JsonResponse(
                $data,
                400
            );
        }

        if ($request->isMethod('GET')) {
            $convertedData = $this->dataService->convertObjectToArray($messages);
            $data = $this->dataService->rebuildPropertyArray($convertedData, 'user', [
                'id',
                'nickname'
            ]);
            $data = $this->dataService->removeProperties($data, [
                'room'
            ]);

            return new JsonResponse(
              array_reverse($data)
            );
        }

        if ($request->isMethod('POST')) {
            $parameter = json_decode($request->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  'Invalid json!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            if (!array_key_exists('userId', $parameter)) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  'Json must contain userId!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            if (!array_key_exists('message', $parameter)) {
                $data = [
                    'error' => [
                        'status' => 406,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  'Json must contain message!'
                    ]
                ];

                return new JsonResponse(
                    $data,
                    406
                );
            }

            $user = $this->userRepository->findOneBy(['id' => $parameter['userId']]);

            if (is_null($user)) {
                $data = [
                    'error' => [
                        'status' => 404,
                        'source' => [
                            'pointer' => $request->getUri()
                        ],
                        'message' =>  sprintf('User with id %s don\'t exists', $user)
                    ]
                ];

                return new JsonResponse(
                    $data,
                    404
                );
            }

            $this->chatService->createMessage($user, $room, $parameter['message']);

            $data = [
                'notification' => [
                    'status' => 200,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' => sprintf('Message saved in chat room with id %s', $id)
                ]
            ];

            return new JsonResponse(
                $data
            );
        }

        foreach ($messages as $message) {
            $this->chatRoomMessageRepository->deleteEntry($message);
        }

        $data = [
            'notification' => [
                'status' => 200,
                'source' => [
                    'pointer' => $request->getUri()
                ],
                'message' => 'All messages deleted!'
            ]
        ];

        return new JsonResponse(
            $data
        );
    }

    /**
     * @Route("/api/chat/{id}/messages/{messageId}", name="api_chat_by_id_message_by_id", methods={"GET", "PATCH", "DELETE"}, requirements={"id" = "\d+", "messageId" = "\d+"})
     */
    public function chatByIdMessageById(
        int $id,
        int $messageId
    ): Response
    {
        //TODO: everything with jwt oauth2.0



        return new JsonResponse();
    }

    /**
     * @Route("/api/chat/{id}/members", name="api_chat_by_id_members", methods={"GET", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatByIdMembers(
        Request $request,
        int $id
    ): Response
    {
        return new JsonResponse(

        );
    }

    /**
     * @Route("/api/chat/{id}/parameters", name="api_chat_by_id_parameters", methods={"GET", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatByIdParameters(
        Request $request,
        int $id
    ): Response
    {
        return new JsonResponse(

        );
    }

    /**
     * @Route("/api/chat/{id}/settings", name="api_chat_by_id_settings", methods={"GET", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatByIdSettings(
        Request $request,
        int $id
    ): Response
    {
        return new JsonResponse(

        );
    }

    /**
     * @Route("/api/chat/{id}/image", name="api_chat_by_id_image", methods={"GET", "DELETE", "POST"}, requirements={"id" = "\d+"})
     */
    public function chatByIdImage(
        Request $request,
        int $id
    ): Response
    {
        $room = $this->chatRoomRepository->findOneBy(['id' => $id]);

        if (is_null($room)) {
            $data = [
                'error' => [
                    'status' => 404,
                    'source' => [
                        'pointer' => $request->getUri()
                    ],
                    'message' =>  sprintf('Chat room with id %s don\'t exists', $id)
                ]
            ];

            return new JsonResponse(
                $data,
                404
            );
        }

        return new JsonResponse(

        );
    }


}