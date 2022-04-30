<?php declare(strict_types=1);

namespace App\Controller\API;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatController
{
    /**
     * @Route("/api/chat", name="api_chat", methods={"POST"})
     */
    public function chat(): Response
    {
        //TODO: everything with jwt oauth2.0

        //TODO: POST -> create room // Body-> userId, type // Response -> json: id, userId

        return new JsonResponse();
    }

    /**
     * @Route("/api/chat/{id}", name="api_chat_by_id", methods={"GET", "PUT", "DELETE"}, requirements={"id" = "\d+"})
     */
    public function chatById(
        int $id
    ): Response
    {
        //TODO: everything with jwt oauth2.0

        //TODO: GET -> json: shows all room settings and parameter, type and separate api link for room image

        //TODO: PUT -> request body: json -> "add": {userId} (if type = private only two user in room at all), "settings": {}, "parameter": {}, "name": "foo"
        //TODO: PUT -> request attached image -> image path; Response -> json: room with changes

        //TODO: DELETE -> delete everything: room and other dependencies

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

        return new JsonResponse();
    }

    /**
     * @Route("/api/chat/{id}/messages/{messageId}", name="api_chat_by_id_message_by_id", methods={"GET", "PUT", "DELETE"}, requirements={"id" = "\d+", "messageId" = "\d+"})
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