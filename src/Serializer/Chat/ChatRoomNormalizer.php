<?php declare(strict_types=1);

namespace App\Serializer\Chat;

use App\Entity\ChatRoom;

class ChatRoomNormalizer
{
    public function normalize(
        ChatRoom $room,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'id' => $room->getId(),
            'type' => $room->getType()->getType(),
            'settings' => json_decode($room->getSettings(), true),
            'parameter' => json_decode($room->getParameter(), true),
            'name' => $room->getName()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof ChatRoom;
    }
}
