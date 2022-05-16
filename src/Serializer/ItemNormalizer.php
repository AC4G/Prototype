<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Item;

class ItemNormalizer
{
    public function normalize(
        Item $item,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'id' => $item->getId(),
            'name' => $item->getName(),
            'gameName' => $item->getGameName(),
            'parameter' => json_decode($item->getParameter(), true),
            'creationDate' => $item->getCreationDate(),
            'creator' => [
                'id' => $item->getUser()->getId(),
                'nickname' => $item->getUser()->getNickname()
            ]
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof Item;
    }
}
