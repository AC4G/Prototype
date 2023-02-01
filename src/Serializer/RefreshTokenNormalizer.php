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
        array $context = []
    ): bool
    {
        return $data instanceof RefreshToken;
    }


}
