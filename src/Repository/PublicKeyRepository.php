<?php

namespace App\Repository;

use App\Entity\PublicKey;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method PublicKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicKey[]    findAll()
 * @method PublicKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicKeyRepository extends AbstractRepository
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, PublicKey::class
        );
    }

    public function getPublicKeyByUuidFromCache(
        string $uuid
    ): null|PublicKey
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);

        return $this->cache->get('public_key_' . $uuid, function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->findOneBy(['user' => $user]);
        });
    }


}
