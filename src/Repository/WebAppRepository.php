<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\WebApp;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method WebApp|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebApp|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebApp[]    findAll()
 * @method WebApp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebAppRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, WebApp::class
        );
    }

    public function getWebAppFromCacheByClient(
        null|Client $client
    ): null|WebApp
    {
        if (is_null($client)) {
            return null;
        }

        return $this->cache->get('webApp_'. $client->getClientId(), function (ItemInterface $item) use ($client){
            $item->expiresAfter(86400);

            return $this->findOneBy(['client' => $client]);
        });
    }


}
