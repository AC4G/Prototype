<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class UserService
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function getUserByUuidFromCache(
        string $uuid
    )
    {
        return $this->cache->get('user_'. $uuid, function (ItemInterface $item) use ($uuid) {
            $item->expiresAfter(86400);

            return $this->userRepository->findOneBy(['uuid' => $uuid]);
        });
    }


}
