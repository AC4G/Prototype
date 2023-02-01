<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\AccessToken;

class AccessTokenNormalizer
{
    public function normalize(
        AccessToken $accessToken,
        string $format = null,
        array $context = []
    ): array
    {
        $user = $accessToken->getUser();
        $project = $accessToken->getProject();

        return [
            'id' => $accessToken->getId(),
            'token' => $accessToken->getAccessToken(),
            'scopes' => $accessToken->getScopes(),
            'project' => [
                'id' => $project->getId(),
            ],
            'user' => [
                'id' => is_null($user) ? null : $user->getId(),
                'nickname' => is_null($user) ? null : $user->getNickname(),
                'roles' => is_null($user) ? null : $user->getRoles()
            ],
            'creationDate' => $accessToken->getCreationDate(),
            'expireDate' => $accessToken->getExpireDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
        return $data instanceof AccessToken;
    }


}
