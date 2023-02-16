<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;

final class UserNormalizer
{
    public function normalize(
        User $user,
        string $format = null,
        string $context = null
    ): array
    {
        if ('user_api' === $context) {
            return [
                'uuid' => $user->getUuid(),
                'nickname' => $user->getNickname(),
                'email' => $user->getEmail(),
                'profilePic' => $user->getProfilePic(),
                'isPrivate' => $user->isPrivate(),
            ];
        }

        return [
            'id' => $user->getId(),
            'uuid' => $user->getUuid(),
            'nickname' => $user->getNickname(),
            'email' => $user->getEmail(),
            'profilePic' => $user->getProfilePic(),
            'isPrivate' => $user->isPrivate(),
            'creation' => $user->getCreationDate(),
            'isGoogleAuthenticatorEnabled' => $user->isGoogleAuthenticatorEnabled(),
            'isTwoFaVerified' => $user->isTwoFaVerified()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        string $context = null
    ): bool
    {
            return $data instanceof User;
    }
}
