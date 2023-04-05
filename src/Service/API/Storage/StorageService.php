<?php declare(strict_types=1);

namespace App\Service\API\Storage;

use App\Entity\Project;
use App\Entity\Storage;
use App\Entity\TotalStorageUsage;
use App\Repository\StorageRepository;
use App\Repository\ProjectRepository;
use Symfony\Contracts\Cache\CacheInterface;
use App\Repository\TotalStorageUsageRepository;

class StorageService
{
    public function __construct(
        private readonly TotalStorageUsageRepository $totalStorageUsageRepository,
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
        $value = is_array($value) ? json_encode($value) : (string)$value;

        $length = strlen($value);

        $storage
            ->setProject($project)
            ->setKey($key)
            ->setValue($value)
            ->setLength($length)
        ;

        $this->addLengthToTotalStorageUsage($project, $length);
        $this->cache->delete('total_storage_usage_' . $projectId);

        $this->storageRepository->persistAndFlushEntity($storage);
    }

    private function addLengthToTotalStorageUsage(
        Project $project,
        int $length
    ): void
    {
        $totalStorageUsage = $this->totalStorageUsageRepository->findOneBy(['project' => $project]);

        if (!is_null($totalStorageUsage)) {
            $totalStorageUsage->setTotalUsage($totalStorageUsage->getTotalUsage() + $length);
            $this->totalStorageUsageRepository->flushEntity();
            return;
        }

        $totalStorageUsage = (new TotalStorageUsage())
            ->setProject($project)
            ->setTotalUsage($length);
        $this->totalStorageUsageRepository->persistAndFlushEntity($totalStorageUsage);
    }

    public function updateStorage(
        int $projectId,
        string $key,
        string|array|int $value
    ): void
    {
        $storage = $this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $key);

        $value = is_array($value) ? json_encode($value) : (string)$value;
        $length = strlen($value);

        $this->storageRepository->updateValueById($storage->getId(), $value, $length);
        $this->totalStorageUsageRepository->updateTotalStorageUsage($projectId, $length - $storage->getLength());

        $this->cache->delete('storage_' . $projectId . '_' . $key);
        $this->cache->delete('total_storage_usage_' . $projectId);
    }

    public function deleteStorage(
        int $projectId,
        string $key,
    ): void
    {
        $storage = $this->storageRepository->getStorageByProjectIdAndKeyFromCache($projectId, $key);

        $this->totalStorageUsageRepository->updateTotalStorageUsage($projectId, -$storage->getLength());
        $this->cache->delete('storage_' . $projectId . '_' . $key);
        $this->cache->delete('total_storage_usage_' . $projectId);

        $this->storageRepository->deleteById($storage->getId());
    }


}