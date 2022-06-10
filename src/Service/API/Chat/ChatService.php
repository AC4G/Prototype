<?php declare(strict_types=1);

namespace App\Service\API\Chat;

use DateTime;
use App\Entity\User;
use App\Entity\ChatRoom;
use App\Entity\ChatMessage;
use App\Entity\ChatRoomType;
use App\Entity\ChatRoomMember;
use App\Repository\ChatRoomRepository;
use App\Repository\ChatRoomTypeRepository;
use App\Repository\ChatRoomMemberRepository;
use App\Repository\ChatRoomMessageRepository;


class ChatService
{
    public function __construct(
        private ChatRoomMessageRepository $chatRoomMessageRepository,
        private ChatRoomMemberRepository $chatRoomMemberRepository,
        private ChatRoomTypeRepository $chatRoomTypeRepository,
        private ChatRoomMessageRepository $messageRepository,
        private ChatRoomRepository $chatRoomRepository
    )
    {
    }

    public function createRoom(
        array $parameters
    ): ChatRoom
    {
        $type = $this->chatRoomTypeRepository->findOneBy(['type' => $parameters['type']]);

        if (is_null($type)) {
            $type = new ChatRoomType();

            $type
                ->setType($parameters['type'])
            ;

            $this->chatRoomTypeRepository->persistAndFlushEntity($type);
        }

        $room = new ChatRoom();

        $room
            ->setParameter(array_key_exists('parameter', $parameters) ? json_encode($parameters['parameter']) : '{}')
            ->setName(array_key_exists('name', $parameters) ? $parameters['name'] : '')
            ->setSettings(array_key_exists('settings', $parameters) ? json_encode($parameters['settings']) : '{}')
            ->setType($type)
        ;

        $this->chatRoomRepository->persistAndFlushEntity($room);

        return $room;
    }

}
