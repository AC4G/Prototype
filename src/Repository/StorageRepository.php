<?php

namespace App\Repository;

use App\Entity\Storage;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method Storage|null find($id, $lockMode = null, $lockVersion = null)
 * @method Storage|null findOneBy(array $criteria, array $orderBy = null)
 * @method Storage[]    findAll()
 * @method Storage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StorageRepository extends AbstractRepository
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Storage::class
        );
    }

    public function getStorageByProjectIdAndKeyFromCache(
        int $projectId,
        string $key
    ): null|Storage
    {
        return $this->cache->get('storage_' . $projectId . '_' . $key, function (ItemInterface $item) use ($projectId, $key) {
            $item->expiresAfter(86400);

            $project = $this->projectRepository->getProjectByIdFromCache($projectId);

            return $this->findOneBy(['project' => $project, 'key' => $key]);
        });
    }

    public function updateValueById(
        int $id,
        string $value
    ): void
    {
        $query = $this->createQueryBuilder('s')
            ->update('App:Storage', 's')
            ->set('s.value', ':value')
            ->where('s.id = :id')
            ->setParameter('value', $value)
            ->setParameter('id', $id)
            ->getQuery()
        ;

        $query->execute();
    }

    public function deleteById(
        int $id
    ): void
    {
        $query = $this->createQueryBuilder('s')
            ->delete('App:Storage', 's')
            ->where('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
        ;

        $query->execute();
    }


}
