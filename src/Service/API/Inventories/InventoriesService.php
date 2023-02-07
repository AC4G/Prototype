<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Inventory;
use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use App\Serializer\InventoryNormalizer;
use App\Repository\InventoryRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class InventoriesService
{
    public function __construct(
        private readonly InventoryRepository $inventoryRepository,
        private readonly InventoryNormalizer $inventoryNormalizer,
        private readonly UserRepository $userRepository,
        private readonly ItemRepository $itemRepository,
        private readonly CacheInterface $cache

    )
    {
    }

    public function updateInventory(
        array $parameter,
        Inventory $inventory
    ): void
    {
        if (array_key_exists('amount', $parameter)) {
            $inventory->setAmount($inventory->getAmount() + $parameter['amount']);
        }

        if (!array_key_exists('parameter', $parameter)) {
            $this->inventoryRepository->flushEntity();

            return;
        }

        $parameters = json_decode($inventory->getParameter(), true);

        $newParameters = $parameter['parameter'];

        if (count($parameters) === 0) {
            $parameters = $newParameters;

            $this->setParameterAndSave($inventory, $parameters);
        }

        foreach ($newParameters as $parameterKey => $newValue) {
            foreach ($parameters as $key => $oldValue) {
                if ($parameterKey === $key) {
                    $parameters[$parameterKey] = is_numeric($oldValue) && is_numeric($newValue) ? $oldValue + $newValue : $newValue;
                    continue 2;
                }

                $parameters[$parameterKey] = $newValue;
            }
        }

        $this->setParameterAndSave($inventory, $parameters);
    }

    public function createEntryInInventory(
        array $parameter,
        User $user,
        Item $item
    ): void
    {
        $inventory = new Inventory();
        $user = $this->userRepository->findOneBy(['id' => $user->getId()]);
        $item = $this->itemRepository->findOneBy(['id' => $item->getId()]);

        $inventory
            ->setUser($user)
            ->setItem($item)
            ->setAmount($parameter['amount'])
            ->setParameter((array_key_exists('parameter', $parameter) ? json_encode($parameter['parameter']) : '{}'))
        ;

        $this->inventoryRepository->persistEntity($inventory);
        $this->inventoryRepository->flushEntity();
    }

    public function deleteParameter(
        Inventory $inventory,
        array $parameters
    ): void
    {
        $allParameters = json_decode($inventory->getParameter(), true);

        $cleanedParameter = [];

        foreach ($parameters as $parameterKey => $value) {
            foreach ($allParameters as $key => $oldValue) {
                if ($parameterKey !== $key) {
                    $cleanedParameter[$key] = $oldValue;
                }
            }
        }

        $this->setParameterAndSave($inventory, $cleanedParameter);
    }

    private function setParameterAndSave(
        Inventory $inventory,
        array $parameters
    ): void
    {
        $inventory->setParameter(json_encode($parameters));

        $this->inventoryRepository->flushEntity();
    }

    public function prepareData(
        array|Inventory $inventories,
        ?string $format = null,
        array $context = []
    ): array
    {
        if (is_object($inventories)) {
            return $this->inventoryNormalizer->normalize($inventories, $format, $context);
        }

        $inventoryList = [];

        foreach ($inventories as $inventory) {
            $inventoryList[] = $this->inventoryNormalizer->normalize($inventory, $format, $context);
        }

        return $inventoryList;
    }

    public function getItemFromCacheByUuidAndItemId(
        string $uuid,
        int $itemId,
        User $user,
        Item $item
    ): null|Inventory
    {
        return $this->cache->get('inventory_' . $uuid . '_item_' . $itemId,function (ItemInterface $cacheItem) use ($user, $item) {
            $cacheItem->expiresAfter(86400);

            return $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);
        });
    }


}
