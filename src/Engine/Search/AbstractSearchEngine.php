<?php declare(strict_types=1);

namespace App\Engine\Search;

use App\Service\API\Item\ItemService;
use App\Service\API\Project\ProjectService;
use App\Service\API\Inventories\InventoryService;

abstract class AbstractSearchEngine
{
    private array $amountOfOccurrence = [];

    public function __construct(
        private readonly ItemService
        |InventoryService
        |ProjectService $service
    )
    {
    }

    protected function findItemByParameters(
        array $items,
        string $parameters,
        string $columnName
    ): array
    {
        $parameters = $this->prepareParameters($parameters);
        $foundContent = $this->findItemBySearchParameters($items, $parameters, $this->buildColumnFunctionName($columnName));

        if (is_string($parameters)) {
            return $this->service->prepareData($foundContent);
        }

        return $this->sortContentByRelevance($foundContent, count($parameters));
    }

    private function buildColumnFunctionName(
        string $columnName
    ): string
    {
        return 'get' . $columnName;
    }

    private function findItemBySearchParameters(
        array $items,
        string|array $parameters,
        string $columnFunctionName
    ): array
    {
        $foundContent = [];

        foreach ($items as $item) {
            if (is_string($parameters)) {
                if ($this->containsParameterInSearchedItemColumn($item, $columnFunctionName, $parameters)) {
                    $foundContent[] = $item;
                }

                continue;
            }

            foreach ($parameters as $parameter) {
                if ($this->containsParameterInSearchedItemColumn($item, $columnFunctionName, $parameter)) {
                    (array_key_exists($item->getId(), $this->amountOfOccurrence)) ? $this->amountOfOccurrence[$item->getId()] = $this->amountOfOccurrence[$item->getId()] + 1 : $this->amountOfOccurrence[$item->getId()] = 1;

                    if (!array_key_exists($item->getId(), $foundContent)) $foundContent[$item->getId()] = $item;
                }
            }
        }

        return $foundContent;
    }

    private function containsParameterInSearchedItemColumn(
        Object $item,
        string $columnFunctionName,
        string $parameter
    ): bool
    {
        return strpos($item->$columnFunctionName(), $parameter) > 0;
    }

    private function prepareParameters(
        string $parameters
    ): array|string
    {
        if ($this->parameterContainsNeedle(',', $parameters)) {
            $parameters =  $this->explodeByCommaAndTrimSpacesFromParameter($parameters);
        }

        if (is_array($parameters)) {
            return $this->createPreciseListParameters($parameters);
        }

        if ($this->parameterContainsNeedle('->', $parameters)) {
            $parameters = $this->createPreciseParameter($parameters);
        }

        return $this->parameterContainsNeedle('->', $parameters) ? $this->createPreciseParameter($parameters) : $parameters;
    }

    private function explodeByCommaAndTrimSpacesFromParameter(
        string $parameters
    ): array
    {
        return array_map('trim', explode(',', $parameters));
    }

    private function parameterContainsNeedle(
        string $needle,
        string $parameter
    ): bool
    {
        return strpos($parameter, $needle) > 0;
    }

    private function createPreciseListParameters(
        array $parameters
    ): array
    {
        foreach ($parameters as $key => $parameter) {
            if ($this->parameterContainsNeedle('->', $parameter)) {
                $parameters[$key] = $this->createPreciseParameter($parameter);
            }
        }

        return $parameters;
    }

    private function createPreciseParameter(
        string $parameter
    ): string
    {
        $keyWithValue = explode('->', $parameter);
        return '"' . $keyWithValue[0] . '": ' . (is_numeric($keyWithValue[1]) ? (int)$keyWithValue[1] : '"' . $keyWithValue[1] . '"');
    }

    private function sortContentByRelevance(
        array $foundContent,
        int $amountOfParameters
    ): array
    {
        $relevance = $this->calculateRelevanceAndHandOverWithItemIdAsKey($foundContent, $amountOfParameters);
        $itemsWithRelevance = $this->handOverRelevanceToItemsWithRelevance($relevance, $foundContent);
        $this->amountOfOccurrence = [];

        return $this->sortItemsByRelevance($itemsWithRelevance);
    }

    private function sortItemsByRelevance(
        array $itemsWithRelevance
    ): array
    {
        uasort($itemsWithRelevance, function ($a, $b) {
            if ($a['relevance'] === $b['relevance']) {
                return 0;
            }

            return ($a['relevance'] < $b['relevance']) ? 1 : -1;
        });

        return $itemsWithRelevance;
    }

    private function handOverRelevanceToItemsWithRelevance(
        array $relevance,
        array $content
    ): array
    {
        $filteredByRelevance = [];

        foreach ($relevance as $key => $percent) {
            $normalizedItem = $this->service->prepareData($content[$key]);
            $normalizedItem['relevance'] = $percent;
            $filteredByRelevance[] = $normalizedItem;
        }

        return $filteredByRelevance;
    }

    private function calculateRelevanceAndHandOverWithItemIdAsKey(
        array $content,
        int $amountOfParameters
    ): array
    {
        $percentageOfOccurrence = [];

        foreach ($content as $key => $item) {
            if (array_key_exists($key, $this->amountOfOccurrence)) {
                $percentageOfOccurrence[$key] = (100 / $amountOfParameters) * $this->amountOfOccurrence[$key];
            }
        }

        return $percentageOfOccurrence;
    }


}
