<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use App\Entity\User;
use App\Entity\Item;
use App\Entity\Inventory;
use App\Service\DataService;
use App\Repository\UserRepository;
use App\Repository\InventoryRepository;

class InventoriesService
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private UserRepository $userRepository,
        private DataService $dataService
    )
    {
    }

    public function getInventoryByProperty(
        string $property
    ): ?array
    {
        if (is_numeric($property)) {
            $user = $this->userRepository->findOneBy(['id' => (int)$property]);

            return (is_null($user)) ? null : $this->inventoryRepository->findBy(['user' => $user]);
        }

        $user = $this->userRepository->findOneBy(['nickname' => $property]);

        return (is_null($user)) ? null : $this->inventoryRepository->findBy(['user' => $user]);
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

        foreach ($parameter as $parameterKey => $parameterValue) {
            if ($parameterKey === 'parameter') {
                foreach ($parameterValue as $secondKey => $value) {
                    foreach ($parameters as $key => $oldValue) {
                        if ($secondKey === $key) {
                            $parameters[$key] = is_numeric($value) ? $oldValue + $value : $value;

                            continue 2;
                        }

                        $parameters[$secondKey] = $value;
                    }
                }
            }
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
