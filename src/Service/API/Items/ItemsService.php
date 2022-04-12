<?php declare(strict_types=1);

namespace App\Service\API\Items;

use App\Entity\Item;
use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ItemsService
{
    public function __construct(
        private DenormalizerInterface $denormalize,
        private NormalizerInterface $normalizer,
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
        array $newParameter
    ): ?Item
    {
        $item = $this->itemRepository->findOneBy(['id' => $property]);

        if (is_null($item)) {
            return null;
        }

        $normalizedItem = $this->normalizer->normalize($item);

        foreach ($newParameter as $key => $parameter) {
            if ($key === 'user' || $key === 'creationDate' || $key === 'path') {
                continue;
            }

            if ($key === 'parameter' && array_key_exists($key, $normalizedItem)) {
                $data = json_decode($normalizedItem[$key], true);

                foreach ($parameter as $subParameterKey => $secondParameter) {
                    $data[$subParameterKey] = $secondParameter;
                }

                $normalizedItem[$key] = json_encode($data);

                continue;
            }

            if (array_key_exists($key, $normalizedItem)) {
                $normalizedItem[$key] = $parameter;
            }
        }

        $serializer = new Serializer([$this->denormalize]);

        $item = $serializer->denormalize($normalizedItem, Item::class);


        //TODO: find out, why it don't update entry
        $this->itemRepository->flushEntity();

        return $item;
    }
}
