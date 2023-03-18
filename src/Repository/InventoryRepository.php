<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Inventory;
use App\Serializer\InventoryNormalizer;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\InputBag;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends AbstractRepository
{
    public function __construct(
        private readonly InventoryNormalizer $inventoryNormalizer,
        private readonly ItemRepository $itemRepository,
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Inventory::class
        );
    }

    public function getInventoryFromCacheByUuid(
        string $uuid,
        InputBag $inputBag = null,
        User $user = null
    ): array
    {
        if (!is_null($inputBag) && $inputBag->get('filter') === 'true') {
            $inventory = $this->getInventoryFromCacheByUuidWithFilter($uuid, $inputBag);
        } else {
            $inventory = json_decode($this->getInventoryInJsonFromCacheByUuid($uuid, $user) , true);
        }

        return $inventory;
    }

    private function getInventoryInJsonFromCacheByUuid(
        string $uuid,
        User $user = null
    ): string
    {
        return $this->cache->get('inventory_' . $uuid, function (ItemInterface $cacheItem) use ($uuid, $user) {
            $cacheItem->expiresAfter(86400);

            if (is_null($user)) {
                $user = $this->userRepository->getUserByUuidFromCache($uuid);
            }

            $inventories = $this->findBy(['user' => $user]);

            $normalized = [];

            foreach ($inventories as $inventory) {
                $normalized[] = $this->inventoryNormalizer->normalize($inventory, null, 'api');
            }

            return json_encode($normalized);
        });
    }

    private function getInventoryFromCacheByUuidWithFilter(
        string $uuid,
        InputBag $inputBag
    ): array
    {
        $amount = $inputBag->get('amount');
        $projectName = $inputBag->get('projectName');
        $creator = $inputBag->get('creator');

        return $this->cache->get('inventory_' . $uuid . '_' . $amount . '_' . $projectName . '_' . $creator, function (ItemInterface $cacheItem) use ($uuid, $amount, $projectName, $creator) {
            $cacheItem->expiresAfter(86400);

            $user = $this->userRepository->getUserByUuidFromCache($uuid);

            if (!is_null($creator)) {
                $creator = $this->userRepository->getUserByUuidOrNicknameFromCache($creator);
            }

            return $this->findWithFilter($user, $amount, $projectName, $creator);
        });
    }

    public function getItemInInventoryFromCacheByUuidAndItemId(
        string $uuid,
        int $itemId,
    ): array|null
    {
        $inventory = json_decode($this->getInventoryInJsonFromCacheByUuid($uuid), true);

        foreach ($inventory as $item) {
            if ($item['itemId'] === $itemId) {
                return $item;
            }
        }

        return null;
    }

    public function getItemInInventoryByUuidAndItemId(
        string $uuid,
        int $itemId,
    ): null|Inventory
    {
        $user = $this->userRepository->getUserByUuidFromCache($uuid);
        $item = $this->itemRepository->getItemFromCacheById($itemId);

        return $this->findOneBy(['user' => $user, 'item' => $item]);
    }

    public function findWithFilter(
        User $user,
        ?string $amount,
        ?string $projectName,
        ?User $creator
    ): array
    {
        $queryBuilder = $this->createQueryBuilder(alias: 'inv')
            ->join('inv.item','item')
            ->andWhere('inv.user = :user')
            ->setParameter('user', $user)
        ;

        if (!is_null($amount)) {
            $queryBuilder->andWhere('inv.amount = :amount')
                ->setParameter('amount', intval($amount));
        }

        if (!is_null($projectName)) {
            $queryBuilder->join('item.project', 'proj')
                ->andWhere('proj.projectName = :projectName')
                ->setParameter('projectName', $projectName);
        }

        if (!is_null($creator)) {
            $queryBuilder->andWhere('item.user = :creator')
                ->setParameter('creator', $creator);
        }

        return $queryBuilder->getQuery()->getResult();
    }


}
