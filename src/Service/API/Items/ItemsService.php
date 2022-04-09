<?php declare(strict_types=1);

namespace App\Service\API\Items;

use App\Entity\User;
use App\Entity\Item;
use App\Repository\UserRepository;
use App\Repository\ItemRepository;

class ItemsService
{
    public function __construct(
        private ItemRepository $itemRepository,
        private UserRepository $userRepository
    )
    {
    }

    public function getItems(): ?array
    {
        return $this->itemRepository->findAll();
    }

    public function getItemDependentOnProperty(
        string $property
    ): null|Item|array
    {
        if (is_numeric($property)) {
            return $this->itemRepository->findOneBy(['id' => (int)$property]);
        }

        $user = $this->userRepository->findOneBy(['nickname' => $property]);

        if (is_null($user)) {
            return null;
        }

        return $this->itemRepository->findBy(['user' => $user]);
    }

    public function updateItem(
        string $property,
        array $parameter
    ): ?Item
    {
        //TODO: update script with lot of foreach:)

        return null;
    }
}