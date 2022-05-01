<?php declare(strict_types=1);

namespace App\Service\API\Chat;

use App\Entity\User;
use App\Entity\ChatRoom;
use App\Entity\ChatRoomType;
use App\Entity\ChatRoomMember;
use App\Repository\ChatRoomRepository;
use App\Repository\ChatRoomTypeRepository;
use App\Repository\ChatRoomMemberRepository;


class ChatService
{
    public function __construct(
        private ChatRoomMemberRepository $chatRoomMemberRepository,
        private ChatRoomTypeRepository $chatRoomTypeRepository,
        private ChatRoomRepository $chatRoomRepository
    )
    {
    }

    public function createRoom(
        array $parameter,
        User $user
    ): ?ChatRoom
    {
        $roomType = $this->chatRoomTypeRepository->findOneBy(['type' => $parameter['type']]);

        if (is_null($roomType)) {
            $roomType = new ChatRoomType();

            $roomType->setType($parameter['type']);

            $this->chatRoomTypeRepository->persistAndFlushEntity($roomType);
        }

        $room = new ChatRoom();

        $room->setType($roomType);

        $this->chatRoomRepository->persistAndFlushEntity($room);

        $roomMember = new ChatRoomMember();

        $roomMember
            ->setChatRoom($room->getId())
            ->setUser($user)
        ;

        $this->chatRoomMemberRepository->persistAndFlushEntity($roomMember);

        return $room;
    }
}