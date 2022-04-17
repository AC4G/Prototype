<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use DateTime;
use App\Repository\ItemRepository;
use App\Repository\UserRepository;
use App\Repository\InventoryRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class InventoriesService
{
    private array $message = [];

    public function __construct(
        private InventoryRepository $inventoryRepository,
        private NormalizerInterface $normalizer,
        private UserRepository $userRepository,
        private ItemRepository $itemRepository
    )
    {
    }

    public function showInventoryByProperty(
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
        if (!array_key_exists('itemId', $parameter)) {
            $this->message['itemId'] = 'JSON not contain itemId from item';
        }

        if (!array_key_exists('amount', $parameter)) {
            $this->message['amount'] = 'JSON not contain amount of items';
        }

        $user = $this->userRepository->findOneBy((is_numeric($property) ? ['id' => (int)$property] : ['nickname' => $property]));

        if (is_null($user)) {
            $this->message['user'] = 'User not exists!';
            return;
        }

        $item = $this->itemRepository->findOneBy(['id' => $parameter['itemId']]);

        if (is_null($item)) {
            $this->message['item'] = sprintf('Item with id %s do not exists', $parameter['itemId']);
            return;
        }

        $inventory = $this->inventoryRepository->findOneBy(['user' => $user, 'item' => $item]);

        if (is_null($inventory)) {
            $this->message['inventory'] = 'User does not have this item. Please use POST method to add item!';
            return;
        }

        $inventory->setAmount(
            $inventory->getAmount() + $parameter['amount']
        );


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

    public function hasMessages(): bool
    {
        return count($this->message) > 0;
    }

    public function getMessages(): array
    {
        return $this->message;
    }


}