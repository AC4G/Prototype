<?php declare(strict_types=1);

namespace App\Service\API\Items;

use DateTime;
use App\Entity\Item;
use App\Service\DataService;
use App\Repository\UserRepository;
use App\Repository\ItemRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ItemsService
{
    public function __construct(
        private NormalizerInterface $normalizer,
        private ItemRepository $itemRepository,
        private DataService $dataService
    )
    {
    }

    public function getItems(): ?array
    {
        return $this->itemRepository->findAll();
    }

    public function updateItem(
        string $property,
        array $newParameter
    ): ?Item
    {
        $item = $this->itemRepository->findOneBy(['id' => (int)$property]);

        if (is_null($item)) {
            return null;
        }

        $normalizedItem = $this->normalizer->normalize($item);

        foreach ($newParameter as $key => $parameter) {
            if ($key === 'id' || $key === 'user' || $key === 'creationDate' || $key === 'path') {
                continue;
            }

            if ($key === 'parameter') {
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

        foreach ($normalizedItem as $key => $parameter) {
            if ($key === 'id' || $key === 'user') {
                continue;
            }

            if (str_contains($key, 'Date')) {
                $parameter = new DateTime($parameter);
            }

            $method = 'set' . ucfirst($key);
            $item->$method($parameter);
        }

        $this->itemRepository->flushEntity();

        return $item;
    }

    public function prepareData(
        array|object $item
    ): array
    {
        $data = $this->dataService->convertObjectToArray($item);
        $data = $this->dataService->rebuildPropertyArray($data, 'user', [
            'id',
            'nickname',
        ]);
        $data = $this->dataService->removeProperties($data, [
            'path',
        ]);
        return $this->dataService->convertPropertiesToJson($data, [
            'parameter',
        ]);
    }


}
