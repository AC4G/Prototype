<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\DataService;
use App\Repository\UserRepository;
use App\Service\API\Chat\ChatService;
use App\Repository\ChatRoomMemberRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatController
{
    public function __construct(
        private ChatRoomMemberRepository $chatRoomMemberRepository,
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

        //TODO: POST -> create room // Body-> userId, type // Response -> json: id, userId

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

        $roomMembers = $this->chatRoomMemberRepository->findBy(['chatRoomId' => $room->getId()]);

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
        int $id
    ): Response
    {
        //TODO: everything with jwt oauth2.0

        //TODO: GET -> json: shows all room settings and parameter, type and separate api link for room image

        //TODO: PUT -> request body: json -> "add": {userId} (if type = private only two user in room at all), "settings": {}, "parameter": {}, "name": "foo"
        //TODO: PUT -> request attached image -> image path; Response -> json: room with changes

        //TODO: DELETE -> delete everything: room and other dependencies with chat

        return new JsonResponse();
    }

    /**
     * @Route("/api/chat/{id}/messages", name="api_chat_by_id_messages", methods={"GET", "POST", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatByIdMessages(
        int $id
    ): Response
    {
        //TODO: everything with jwt oauth2.0

        //TODO: GET -> show all messages, latest first

        //TODO: POST -> add new message ; json -> "userId": {id}, "message": {message}, "time": {time}, file

        //TODO: DELETE -> delete all messages

        return new JsonResponse();
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
}