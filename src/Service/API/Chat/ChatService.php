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

    public function addUserToRoom(
        User $user,
        ChatRoom $room
    ): bool
    {
        $roomMembers = $this->chatRoomMemberRepository->findBy(['chatRoomId' => $room->getId()]);
        $roomType = $room->getType()->getType();

        if (count($roomMembers) === 2 && $roomType === 'private') {
            return false;
        }

        $roomMember = new ChatRoomMember();

        $roomMember
            ->setUser($user)
            ->setChatRoom($room->getId())
        ;

        $this->chatRoomMemberRepository->persistAndFlushEntity($roomMember);

        return true;
    }

    public function addOrUpdateSettings(
        array $settings,
        ChatRoom $room
    )
    {
        $roomSettings = json_decode($room->getSettings(), true);

        if (!count($roomSettings) > 0) {
            $room
                ->setSettings(json_encode($settings))
            ;

            $this->chatRoomRepository->flushEntity();

            return;
        }

        foreach ($settings as $settingKey => $setting) {
            foreach ($roomSettings as $roomSettingKey => $roomSetting) {
                if ($roomSettingKey === $settingKey) {
                    $roomSettings[$roomSettingKey] = is_numeric($roomSetting) && is_numeric($setting) ? $roomSetting + $setting : $setting;

                    continue 2;
                }

                $roomSettings[$settingKey] = $setting;
            }
        }

        $room
            ->setSettings(json_encode($roomSettings))
        ;

        $this->chatRoomRepository->flushEntity();
    }

    public function addOrUpdateParameter(
        array $parameters,
        ChatRoom $room
    )
    {
        $roomParameters = json_decode($room->getParameter(), true);

        if (!count($roomParameters) > 0) {
            $room
                ->setParameter(json_encode($parameters))
            ;

            $this->chatRoomRepository->flushEntity();

            return;
        }

        foreach ($parameters as $parameterKey => $parameter) {
            foreach ($roomParameters as $roomParameterKey => $roomParameter) {
                if ($roomParameterKey === $parameterKey) {
                    $roomParameters[$roomParameterKey] = is_numeric($roomParameter) && is_numeric($parameter) ? $roomParameter + $parameter : $parameter;

                    continue 2;
                }

                $roomParameters[$parameterKey] = $parameter;
            }
        }

        $room
            ->setParameter(json_encode($roomParameters))
        ;

        $this->chatRoomRepository->flushEntity();
    }

    public function addOrUpdateName(
        string $name,
        ChatRoom $room
    )
    {
        $room
            ->setName($name)
        ;

        $this->chatRoomRepository->flushEntity();
    }

    public function createMessage(
        User $user,
        ChatRoom $chatRoom,
        string $message
    )
    {
        $chatMessage = new ChatMessage();

        $chatMessage
            ->setUser($user)
            ->setMessage($message)
            ->setRoom($chatRoom)
            ->setSendDate(new DateTime())
        ;

        $this->chatRoomMessageRepository->persistAndFlushEntity($chatMessage);
    }

}
