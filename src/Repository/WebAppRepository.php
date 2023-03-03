<?php

namespace App\Repository;

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

    public function getWebAppByClientId(
        string $clientId
    ): null|WebApp
    {
        return $this->cache->get('webApp_'. $clientId, function (ItemInterface $item) use ($clientId){
            $item->expiresAfter(86400);

            return $this->findOneBy(['client' => $clientId]);
        });
    }


}
