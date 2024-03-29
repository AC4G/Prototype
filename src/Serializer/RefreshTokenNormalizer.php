<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\RefreshToken;

final class RefreshTokenNormalizer
{
    public function normalize(
        RefreshToken $refreshToken,
        string $format = null,
        string $context = null
    ): array
    {
        $user = $refreshToken->getUser();
        $project = $refreshToken->getProject();

        return [
            'id' => $refreshToken->getId(),
            'token' => $refreshToken->getRefreshToken(),
            'scopes' => $refreshToken->getScopes(),
            'project' => [
                'id' => $project->getId(),
            ],
            'user' => [
                'id' => is_null($user) ? null : $user->getId(),
                'uuid' => is_null($user) ? null : $user->getUuid(),
                'nickname' => is_null($user) ? null : $user->getNickname(),
                'roles' => is_null($user) ? null : $user->getRoles()
            ],
            'creationDate' => $refreshToken->getCreationDate(),
            'expireDate' => $refreshToken->getExpireDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        string $context = null
    ): bool
    {
        return $data instanceof RefreshToken;
    }


}
