<?php declare(strict_types=1);

namespace App\Serializer;

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

        if ($context === 'api') {
            return [
                'id' => $inventory->getId(),
                'itemId' => $inventory->getItem()->getId(),
                'amount' => $inventory->getAmount(),
                'parameter' => json_decode($inventory->getParameter(), true)
            ];
        }

        if ($context === 'api_all') {
            return [
                'id' => $inventory->getId(),
                'user' => [
                    'uuid' => $user->getUuid()
                ],
                'itemId' => $inventory->getItem()->getId(),
                'amount' => $inventory->getAmount(),
                'parameter' => json_decode($inventory->getParameter(), true)
            ];
        }

        return [
            'itemId' => $inventory->getItem()->getId(),
            'amount' => $inventory->getAmount(),
            'parameter' => json_decode($inventory->getParameter(), true)
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
