<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Entity\Inventory;

class InventoryNormalizer
{
    public function normalize(
        Inventory $inventory,
        ?string $format = null,
        array $context = []
    ): array
    {
        $user = $inventory->getUser();

        return [
            'amount' => $inventory->getAmount(),
            'parameter' => json_decode($inventory->getParameter(), true),
            'user' => [
                'uuid' => $user->getUuid(),
                'nickname' => $user->getNickname()
            ],
            'item' => [
                'id' => $inventory->getItem()->getId(),
                'name' => $inventory->getItem()->getName()
            ]
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof Inventory;
    }
}
