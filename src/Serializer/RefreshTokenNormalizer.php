<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\RefreshToken;

class RefreshTokenNormalizer
{
    public function normalize(
        RefreshToken $refreshToken,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'id' => $refreshToken->getId(),
            'token' => $refreshToken->getRefreshToken(),
            'scopes' => $refreshToken->getScopes(),
            'projectId' => $refreshToken->getProject()->getId(),
            'userId' => $refreshToken->getUser()->getId(),
            'creationDate' => $refreshToken->getCreationDate(),
            'expireDate' => $refreshToken->getExpireDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof RefreshToken;
    }


}
