<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\Inventory;

class InventoryNormalizer
{
    public function normalize(
        Inventory $inventory,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'amount' => $inventory->getAmount(),
            'parameter' => json_decode($inventory->getParameter(), true),
            'user' => [
                'id' => $inventory->getUser()->getId(),
                'nickname' => $inventory->getUser()->getNickname()
            ],
            'item' => [
                'id' => $inventory->getItem()->getId(),
                'name' => $inventory->getItem()->getName(),
                'projectName' => $inventory->getItem()->getProject()->getProjectName()
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
