<?php declare(strict_types=1);

namespace App\Service\API\Storage;

use App\Entity\Storage;
use App\Repository\StorageRepository;
use App\Repository\ProjectRepository;
use Symfony\Contracts\Cache\CacheInterface;

class StorageService
{
    public function __construct(
        private readonly ProjectRepository $projectRepository,
        private readonly StorageRepository $storageRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function saveStorage(
        int $projectId,
        string $key,
        string|array|int $value
    ): void
    {
        $project = $this->projectRepository->findOneBy(['id' => $projectId]);

        $storage = new Storage();

        $storage
            ->setProject($project)
            ->setKey($key)
            ->setValue(is_array($value) ? json_encode($value) : (string)$value)
        ;

        $this->cache->delete('storage_' . $projectId . '_' . $key);

        $this->storageRepository->persistAndFlushEntity($storage);
    }

    public function updateStorage(
        int $projectId,
        string $key,
        string|array|int $value
    ): void
    {
        $storage = $this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $key);

        $this->storageRepository->updateValueById($storage->getId(), is_array($value) ? json_encode($value) : (string)$value);

        $this->cache->delete('storage_' . $projectId . '_' . $key);
    }

    public function deleteStorage(
        int $projectId,
        string $key,
    ): void
    {
        $storage = $this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $key);

        $this->cache->delete('storage_' . $projectId . '_' . $key);

        $this->storageRepository->deleteById($storage->getId());
    }


}