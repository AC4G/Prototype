<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\DataService;
use App\Repository\UserRepository;
use App\Service\API\Chat\ChatService;
use App\Repository\ChatRoomRepository;
use App\Serializer\Chat\ChatRoomNormalizer;
use App\Repository\ChatRoomMemberRepository;
use App\Repository\ChatRoomMessageRepository;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ChatController extends AbstractController
{
    public function __construct(
        private ChatRoomNormalizer $chatRoomNormalizer,
        private CustomResponse $customResponse,
        private ChatService $chatService
    )
    {
    }

    /**
     * @Route("/api/chat", name="api_chat", methods={"POST"})
     */
    public function createChatRoom(
        Request $request
    ): Response
    {
        $parameters = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->customResponse->errorResponse($request, 'Invalid Json!', 406);
        }

        if (!array_key_exists('type', $parameters)) {
            return $this->customResponse->errorResponse($request, 'Type is required', 406);
        }

        $room = $this->chatRoomNormalizer->normalize($this->chatService->createRoom($parameters));

        return new JsonResponse(
            $room
        );
    }

    /**
     * @Route("/api/chat/{id}/members/{property}", name="api_chat_members_by_property", methods={"POST", "DELETE", "GET"}, requirements={"id" = "\d+"})
     */
    public function processChatMemberByProperty(
        Request $request
    ): Response
    {
        return new JsonResponse(

        );
    }

}
