<?php declare(strict_types=1);

namespace App\Service\API\Item;

use App\Entity\Inventory;
use App\Entity\Item;
use App\Serializer\ItemNormalizer;
use App\Repository\ItemRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

final class ItemService
{
    public function __construct(
        private readonly ItemRepository $itemRepository,
        private readonly ItemNormalizer $itemNormalizer,
        private readonly CacheInterface $cache
    )
    {
    }

    public function getItems(): ?array
    {
        return $this->itemRepository->findAll();
    }

    public function updateItem(
        int $id,
        array $itemData,
        array $newParameter
    ): void
    {
        if (array_key_exists('name', $newParameter)) {
            $itemData['name'] = $newParameter['name'];
        }

        if (array_key_exists('parameter', $newParameter) && is_array($newParameter['parameter'])) {
            $parameters = $newParameter['parameter'];
            $data = json_decode($itemData['parameter'], true);

            foreach ($parameters as $key => $parameter) {
                $data[$key] = $parameter;
            }

            $itemData['parameter'] = json_encode($data);
        }

        $this->itemRepository->updateNameAndParameter($id, $itemData);
    }

    public function deleteParameter(
        int $id,
        array $allParameters,
        array $parameters
    ): void
    {
        $cleanedParameter = [];

        foreach ($parameters as $parameterKey => $value) {
            foreach ($allParameters as $key => $oldValue) {
                if ($parameterKey !== $key) {
                    $cleanedParameter[$key] = $oldValue;
                }
            }
        }

        $this->itemRepository->updateParameter($id, $cleanedParameter);
    }

    public function prepareData(
        array|Item $items,
        string $format = null,
        array $context = []
    ): array
    {
        if (is_object($items)) {
            return $this->itemNormalizer->normalize($items, $format, $context);
        }

        $itemsList = [];

        foreach ($items as $item) {
            $itemsList[] = $this->itemNormalizer->normalize($item, $format, $context);
        }

        return $itemsList;
    }

    public function getItemFromCacheById(
        int $id
    ): null|string
    {
        return $this->cache->get('item_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->itemRepository->findOneBy(['id' => $id]);
        });
    }

    public function getItemParameterFromCacheById(
        int $id
    ): null|Inventory
    {
        return $this->cache->get('item_' . $id . '_parameter', function (ItemInterface $item) use ($id) {
            $item->expiresAfter(86400);

            return $this->itemRepository->findOneBy(['id' => $id])->getParameter();
        });
    }


}
