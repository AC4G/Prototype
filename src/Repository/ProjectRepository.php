<?php

namespace App\Repository;

use App\Entity\Project;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Project::class
        );
    }

    public function getProjectByIdFromCache(
        int $id
    ): Project
    {
        return $this->cache->get('project_' . $id, function (ItemInterface $item) use ($id) {
           $item->expiresAfter(86400);

           return $this->findOneBy(['id' => $id]);
        });
    }


}
