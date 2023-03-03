<?php

namespace App\Repository;

use App\Entity\AuthToken;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method AuthToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthToken[]    findAll()
 * @method AuthToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokenRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, AuthToken::class
        );
    }

    public function getAuthTokenFromCacheByCode(
        string $code
    ): null|AuthToken
    {
        return $this->cache->get('authToken_' . $code, function (ItemInterface $item) use ($code) {
            $item->expiresAfter(86400);

            $this->findOneBy(['authToken' => $code]);
        });
    }


}
