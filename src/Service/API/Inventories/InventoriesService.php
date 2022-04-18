<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use App\Entity\Inventory;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Repository\InventoryRepository;

class InventoriesService
{
    private array $message = [];

    public function __construct(
        private InventoryRepository $inventoryRepository,
        private UserRepository $userRepository,
        private ItemRepository $itemRepository
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
        string $property
    )
    {
        $validation = $this->validateData($parameter);

        if (is_null($validation)) {
            return;
        }

        $data = $this->getUserAndItem($parameter, $property);

        $inventory = $this->inventoryRepository->findOneBy(['user' => $data['user'], 'item' => $data['item']]);

        if (is_null($inventory)) {
            $this->message['inventory'] = 'User does not has this item. Please use POST method to add item!';
            return;
        }


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
        string $property
    )
    {
        $validation = $this->validateData($parameter);

        if (is_null($validation)) {
            return;
        }

        $data = $this->getUserAndItem($parameter, $property);

        $inventory = new Inventory();

        $inventory
            ->setUser($data['user'])
            ->setItem($data['item'])
            ->setAmount($parameter['amount'])
            ->setParameter((array_key_exists('parameter', $parameter) ? $parameter['parameter'] : '{}'))
        ;

        $this->inventoryRepository->persistEntity($inventory);
        $this->inventoryRepository->flushEntity();
    }

    private function validateData(
        array $parameter
    ): ?bool
    {
        if (!array_key_exists('itemId', $parameter)) {
            $this->message['itemId'] = 'JSON not contain itemId from item';

            return null;
        }

        if (!array_key_exists('amount', $parameter) && !array_key_exists('parameter', $parameter)) {
            $this->message['amount'] = 'JSON not contain amount of items and parameter. On of them are necessary!';

            return null;
        }

        return true;
    }

    private function getUserAndItem(
        array $parameter,
        string $property
    ): ?array
    {
        $user = $this->userRepository->findOneBy((is_numeric($property) ? ['id' => (int)$property] : ['nickname' => $property]));

        if (is_null($user)) {
            $this->message['user'] = 'User not exists!';
            return null;
        }

        $item = $this->itemRepository->findOneBy(['id' => $parameter['itemId']]);

        if (is_null($item)) {
            $this->message['item'] = sprintf('Item with id %s do not exists', $parameter['itemId']);
            return null;
        }

        return [
            'user' => $user,
            'item' => $item
        ];
    }

    public function hasMessages(): bool
    {
        return count($this->message) > 0;
    }

    public function getMessages(): array
    {
        return $this->message;
    }


}