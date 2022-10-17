<?php declare(strict_types=1);

namespace App\Service\Website\Dashboard;

use App\Serializer\ItemNormalizer;

final class DashboardService
{
    public function __construct(
        public ItemNormalizer $itemNormalizer
    )
    {
    }

    public function normalizeItems(
        array $items
    ): array
    {
        foreach ($items as $key => $item) {
            $items[$key] = $this->itemNormalizer->normalize($item);
        }
       return $items;
    }
}
