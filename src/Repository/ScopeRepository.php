<?php

namespace App\Repository;

use App\Entity\Scope;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Scope|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scope|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scope[]    findAll()
 * @method Scope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScopeRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Scope::class
        );
    }

    public function getScopeById(
        int|string $id
    ): Scope
    {
        return $this->cache->get('scope_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(84600);

            return $this->findOneBy(['id' => $id]);
        });
    }


}
