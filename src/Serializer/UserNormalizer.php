<?php declare(strict_types=1);

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;

class UserNormalizer
{
    public function normalize(
        User|UserInterface $user,
        string $format = null,
        array $context = []
    ): array
    {
        return [
            'id' => $user->getId(),
            'nickname' => $user->getNickname(),
            'email' => $user->getEmail(),
            'profilePic' => $user->getProfilePic(),
            'isPrivate' => $user->isPrivate(),
            'creation' => $user->getCreationDate()
        ];
    }

    public function supportsNormalization(
        $data,
        string $format = null,
        array $context = []
    ): bool
    {
            return $data instanceof User;
    }
}
