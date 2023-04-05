<?php

namespace App\Repository;

use App\Entity\TotalStorageUsage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method TotalStorageUsage|null find($id, $lockMode = null, $lockVersion = null)
 * @method TotalStorageUsage|null findOneBy(array $criteria, array $orderBy = null)
 * @method TotalStorageUsage[]    findAll()
 * @method TotalStorageUsage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TotalStorageUsageRepository extends AbstractRepository
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, TotalStorageUsage::class
        );
    }

    public function getTotalStorageUsageFromCacheByProjectId(
        int $id
    ): null|TotalStorageUsage
    {
        return $this->cache->get('total_storage_usage_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->getTotalStorageUsageByProjectId($id);
        });
    }

    public function getTotalStorageUsageByProjectId(
        int $id
    ): null|TotalStorageUsage
    {
        $queryBuilder = $this->createQueryBuilder(alias: 't')
            ->select('t')
            ->where('t.project.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getSingleResult();
    }

    public function updateTotalStorageUsage(
        int $projectId,
        int $length
    ): void
    {
        $project = $this->projectRepository->getProjectByIdFromCache($projectId);

        $totalStorageUsage = $this->createQueryBuilder('t')
            ->select('t')
            ->where('t.project = :project')
            ->setParameter('project', $project)
            ->getQuery()->getSingleResult();

        $totalStorageUsage->getTotalUsage();

        $this->createQueryBuilder('t')
            ->update('App:TotalStorageUsage', 't')
            ->set('t.totalUsage', ':totalUsage')
            ->where('t.project = :project')
            ->setParameter('totalUsage', $totalStorageUsage->getTotalUsage() + $length)
            ->setParameter('project', $project)
            ->getQuery()->execute();
    }


}