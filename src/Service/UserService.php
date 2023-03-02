<?php declare(strict_types=1);

namespace App\Service;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\Criteria;
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

    public function getUserByUuidOrNicknameFromCache(
        string $identifier
    )
    {
        return $this->cache->get('user_'. $identifier, function (ItemInterface $item) use ($identifier) {
            $item->expiresAfter(86400);

            return $this->userRepository->matching(
                Criteria::create()
                    ->where(Criteria::expr()->contains('uuid', $identifier))
                    ->orWhere(Criteria::expr()->contains('nickname', $identifier))
            );
        });
    }

}
