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

    public function getInventoryInJsonFromCacheByUuid(
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

    public function getInventoryListFromCacheByUuidWithFilter(
        string $uuid,
        InputBag $inputBag
    ): array
    {
        $amount = $inputBag->get('amount');
        $projectName = $inputBag->get('projectName');
        $creator = $inputBag->get('creator');
        $query = $inputBag->get('q');

        if (is_null($amount) && is_null($projectName) && is_null($creator) && is_null($query)) {
            return json_decode($this->getInventoryInJsonFromCacheByUuid($uuid) , true);
        }

        return $this->cache->get('inventory_' . $uuid . '_item_list_filtered_' . $amount . '_' . $projectName . '_' . $creator . '_' . $query, function (ItemInterface $cacheItem) use ($uuid, $amount, $projectName, $creator, $query) {
            $cacheItem->expiresAfter(1800);

            $user = $this->userRepository->getUserByUuidFromCache($uuid);

            if (!is_null($creator)) {
                $creator = $this->userRepository->getUserByUuidOrNicknameFromCache($creator);
            }

            return $this->findWithFilter($user, $amount, $projectName, $creator, $query);
        });
    }

    public function filterInventoryByList(
        string $uuid,
        array $list
    ): array
    {
        $inventories = json_decode($this->getInventoryInJsonFromCacheByUuid($uuid), true);

        $filteredInventory = [];

        foreach ($inventories as $inventory) {
            foreach ($list as $item) {
                if ($inventory['id'] === $item['id']) {
                    $filteredInventory[] = $inventory;
                }
            }
        }

        return $filteredInventory;
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

    private function findWithFilter(
        User $user,
        ?string $amount,
        ?string $projectName,
        ?User $creator,
        ?string $query
    ): array
    {
        $queryBuilder = $this->createQueryBuilder(alias: 'inv')
            ->select('inv.id')
            ->join('inv.item','item')
            ->andWhere('inv.user = :user')
            ->setParameter('user', $user)
        ;

        if (!is_null($amount)) {
            $queryBuilder
                ->andWhere('inv.amount = :amount')
                ->setParameter('amount', intval($amount));
        }

        if (!is_null($projectName)) {
            $queryBuilder
                ->join('item.project', 'proj')
                ->andWhere('proj.projectName = :projectName')
                ->setParameter('projectName', $projectName);
        }

        if (!is_null($creator)) {
            $queryBuilder
                ->andWhere('item.user = :creator')
                ->setParameter('creator', $creator);
        }

        if (!is_null($query)) {
            $queryBuilder
                ->andWhere('item.name LIKE :name')
                ->setParameter('name', '%' . $query . '%');
        }

        return $queryBuilder->getQuery()->getArrayResult();
    }


}
