<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Client::class
        );
    }

    public function getClientFromCacheById(
        string $clientId
    ): null|Client
    {
        return $this->cache->get('client_' . $clientId, function (ItemInterface $item) use ($clientId) {
            $item->expiresAfter(86400);

            return $this->findOneBy(['clientId' => $clientId]);
        });
    }


}
