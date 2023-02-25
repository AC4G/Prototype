<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use App\Entity\Inventory;

final class InventoryNormalizer
{
    public function normalize(
        Inventory $inventory,
        ?string $format = null,
        string $context = null
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
        string $context = null
    ): bool
    {
        return $data instanceof Inventory;
    }
}
