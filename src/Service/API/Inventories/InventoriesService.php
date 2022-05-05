<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Inventory;
use App\Service\DataService;
use App\Repository\InventoryRepository;

class InventoriesService
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private DataService $dataService
    )
    {
    }

    public function updateInventory(
        array $parameter,
        Inventory $inventory
    )
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

        if (count($parameters) > 0) {
            foreach ($newParameters as $parameterKey => $newValue) {
                foreach ($parameters as $key => $oldValue) {
                    if ($parameterKey === $key) {
                        $parameters[$parameterKey] = is_numeric($oldValue) ? $oldValue + $newValue : $newValue;

                        continue 2;
                    }

                    $parameters[$parameterKey] = $newValue;
                }
            }
        }

        if (!count($parameters) > 0) {
            $parameters = $newParameters;
        }

        $inventory->setParameter(json_encode($parameters));

        $this->inventoryRepository->flushEntity();
    }

    public function createEntryInInventory(
        array $parameter,
        User $user,
        Item $item
    )
    {
        $inventory = new Inventory();

        $inventory
            ->setUser($user)
            ->setItem($item)
            ->setAmount($parameter['amount'])
            ->setParameter((array_key_exists('parameter', $parameter) ? $parameter['parameter'] : '{}'))
        ;

        $this->inventoryRepository->persistEntity($inventory);
        $this->inventoryRepository->flushEntity();
    }

    public function deleteParameter(
        Inventory $inventory,
        array $parameters
    )
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

        $inventory->setParameter(json_encode($cleanedParameter));
        $this->inventoryRepository->flushEntity();
    }

    public function prepareInventories(
        array|object $inventory
    ): array
    {
        $data = $this->dataService->convertObjectToArray($inventory);
        $data = $this->dataService->rebuildPropertyArray($data, 'user', [
            'id',
            'nickname'
        ]);
        $data = $this->dataService->rebuildPropertyArray($data, 'item', [
            'id',
            'name',
            'gameName'
        ]);
        $data = $this->dataService->convertPropertiesToJson($data, [
            'parameter'
        ]);
        return $this->dataService->removeProperties($data, [
            'id'
        ]);
    }


}
