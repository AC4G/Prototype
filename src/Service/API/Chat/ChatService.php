<?php declare(strict_types=1);

namespace App\Service\API\Chat;

use App\Entity\User;
use App\Entity\ChatRoom;
use App\Entity\ChatRoomType;
use App\Entity\ChatRoomMember;
use App\Repository\ChatRoomRepository;
use App\Repository\ChatRoomTypeRepository;
use App\Repository\ChatRoomMemberRepository;
use App\Repository\ChatRoomMessageRepository;


class ChatService
{
    public function __construct(
        private ChatRoomMemberRepository $chatRoomMemberRepository,
        private ChatRoomTypeRepository $chatRoomTypeRepository,
        private ChatRoomMessageRepository $messageRepository,
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

    public function deleteRoomAndDependencies(
        ChatRoom $room
    )
    {
        $messages = $this->messageRepository->findBy(['room' => $room]);

        if (count($messages) > 0) {
            foreach ($messages as $message) {
                $this->messageRepository->deleteEntry($message);
            }
        }

        $members = $this->chatRoomMemberRepository->findBy(['chatRoomId' => $room->getId()]);

        if (count($members) > 0) {
            foreach ($members as $member) {
                $this->chatRoomMemberRepository->deleteEntry($member);
            }
        }

        $this->chatRoomRepository->deleteEntry($room);
    }


}
