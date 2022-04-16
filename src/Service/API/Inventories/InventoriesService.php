<?php declare(strict_types=1);

namespace App\Service\API\Inventories;

use App\Entity\Inventory;
use App\Repository\UserRepository;
use App\Repository\InventoryRepository;
use Symfony\Component\HttpFoundation\Request;

class InventoriesService
{
    private array $message = [];

    public function __construct(
        private InventoryRepository $inventoryRepository,
        private UserRepository $userRepository
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
        if (!array_key_exists('id', $parameter)) {
            $this->message['id'] = 'JSON not contain id from item';
        }

        if (!array_key_exists('amount', $parameter)) {
            $this->message['amount'] = 'JSON not contain amount of items';
        }

        $user = $this->userRepository->findOneBy((is_numeric($property) ? ['id' => (int)$property] : ['nickname' => $property]));

        if (is_null($user)) {
            $this->message['user'] = 'User not exists!';
        }

        if (count($this->message) < 1) {

        }
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