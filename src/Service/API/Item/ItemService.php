<?php declare(strict_types=1);

namespace App\Service\API\Item;

use App\Entity\Item;
use App\Serializer\ItemNormalizer;
use App\Repository\ItemRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class ItemService
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private ItemRepository $itemRepository,
        private ItemNormalizer $itemNormalizer
    )
    {
    }

    public function getItems(): ?array
    {
        return $this->itemRepository->findAll();
    }

    public function updateItem(
        Item $item,
        array $newParameter
    )
    {
        $normalizedItem = $this->normalizer->normalize($item);

        if (array_key_exists('name', $newParameter)) {
            $item
                ->setName($newParameter['name'])
            ;
        }

        if (array_key_exists('parameter', $newParameter) && is_array($newParameter['parameter'])) {
            $parameters = $newParameter['parameter'];
            $data = json_decode($normalizedItem['parameter'], true);

            foreach ($parameters as $key => $parameter) {
                $data[$key] = $parameter;
            }

            $item->setParameter(json_encode($data));
        }

        $this->itemRepository->flushEntity();
    }

    public function deleteParameter(
        array $parameters,
        Item $item
    )
    {
        $allParameters = json_decode($item->getParameter(), true);

        $cleanedParameter = [];

        foreach ($parameters as $parameterKey => $value) {
            foreach ($allParameters as $key => $oldValue) {
                if ($parameterKey !== $key) {
                    $cleanedParameter[$key] = $oldValue;
                }
            }
        }

        $item->setParameter(json_encode($cleanedParameter));
        $this->itemRepository->flushEntity();
    }

    public function prepareData(
        array|Item $items,
        array $context = []
    ): array
    {
        if (is_object($items)) {
            return $this->itemNormalizer->normalize($items, null, $context);
        }

        $itemsList = [];

        foreach ($items as $item) {
            $itemsList[] = $this->itemNormalizer->normalize($item, null, $context);
        }

        return $itemsList;
    }


}
