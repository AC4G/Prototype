<?php

namespace App\Repository;

use App\Entity\User;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

class FileRepository
{
    public function __construct(
        private readonly CacheInterface $cache
    )
    {
    }

    public function getProfilePictureByUser(
        User $user
    ): string|null
    {
        return $this->cache->get('profile_picture_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            $files = glob('../assets/files/profile/' . $user->getNickname() . '/*');

            foreach ($files as $file) {
                if (is_file($file) && pathinfo($file)['filename'] === $user->getNickname()) {
                    return file_get_contents($file);
                }
            }

            return null;
        });
    }


}
