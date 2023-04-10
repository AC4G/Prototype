<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Organisation;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Organisation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Organisation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Organisation[]    findAll()
 * @method Organisation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganisationRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Organisation::class
        );
    }

    public function getOrganisationFromCacheById(
        int $id
    ): null|Organisation
    {
        return $this->cache->get('organisation_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(84600);

            return $this->findOneBy(['id' => $id]);
        });
    }


}
