<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\PublicKey;

final class PublicKeyNormalizer
{
    public function normalize(
        PublicKey $key,
        string $format = null,
        string $context = null
    ): array|null
    {
        return [
            'user' => [
                'uuid' => $key->getUser()->getUuid(),
            ],
            'key' => $key->getKey(),
            'creationDate' => $key->getCreationDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof PublicKey;
    }
}
