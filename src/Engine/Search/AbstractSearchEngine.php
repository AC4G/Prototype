<?php declare(strict_types=1);

namespace App\Engine\Search;

use App\Service\API\Items\ItemsService;
use App\Service\API\Project\ProjectService;
use App\Service\API\Inventories\InventoriesService;

abstract class AbstractSearchEngine implements InterfaceSearchEngine
{
    private array $amountOfOccurrence = [];

    public function __construct(
        private ItemsService
        |InventoriesService
        |ProjectService $service
    )
    {
    }

    protected function preparePhraseParameter(
        string $parameter
    ): array|string
    {
        if (strpos($parameter, ',') > 0) {
            $parameters =  array_map('trim',explode(',', $parameter));

            foreach ($parameters as $key => $parameter) {
                if (strpos($parameter, '->') > 0) {
                    $keyWithValue = explode('->', $parameter);
                    $parameters[$key] = '"' . $keyWithValue[0] . '": ' . (is_numeric($keyWithValue[1]) ? (int)$keyWithValue[1] : '"' . $keyWithValue[1] . '"');
                }
            }

            return $parameters;
        }

        if (strpos($parameter, '->') > 0) {
            $keyWithValue = explode('->', $parameter);
            $parameter = '"' . $keyWithValue[0] . '": ' . (is_numeric($keyWithValue[1]) ? (int)$keyWithValue[1] : '"' . $keyWithValue[1] . '"');
        }

        return $parameter;
    }

    protected function findItemByParameter(
        array $items,
        array|string $parameters
    ): array
    {
        $foundContent = [];

        foreach ($items as $item) {
            if (is_string($parameters)) {
                if (strpos($item->getParameter(), $parameters) > 0) {
                    $foundContent[] = $item;
                }

                continue;
            }

            foreach ($parameters as $parameter) {
                if (strpos($item->getParameter(), $parameter) > 0) {
                    (array_key_exists($item->getId(), $this->amountOfOccurrence)) ? $this->amountOfOccurrence[$item->getId()] = $this->amountOfOccurrence[$item->getId()] + 1 : $this->amountOfOccurrence[$item->getId()] = 1;

                    if (!array_key_exists($item->getId(), $foundContent)) $foundContent[$item->getId()] = $item;
                }
            }
        }

        if (is_string($parameters)) {
            return $this->service->prepareData($foundContent);
        }

        return $this->filterContentByOccurrenceOfAllPhrases($foundContent, count($parameters));
    }

    //relevance of the searched items
    protected function filterContentByOccurrenceOfAllPhrases(
        array $foundContent,
        int $amountOfParameters
    ): array
    {
        $filteredByRelevance = [];
        $percentageOfOccurrence = [];

        foreach ($foundContent as $key => $item) {
            if (array_key_exists($key, $this->amountOfOccurrence)) {
                $percentageOfOccurrence[$key] = (100 / $amountOfParameters) * $this->amountOfOccurrence[$key];
            }
        }

        foreach ($percentageOfOccurrence as $key => $percent) {
            if ($percent >= 75) {
                $normalizedItem = $this->service->prepareData($foundContent[$key]);
                $normalizedItem['relevance'] = $percent;
                $filteredByRelevance[] = $normalizedItem;
            }
        }

        uasort($filteredByRelevance, function ($a, $b) {
            if ($a['relevance'] === $b['relevance']) {
                return 0;
            }

            return ($a['relevance'] < $b['relevance']) ? 1 : -1;
        });

        $this->amountOfOccurrence = [];

        return $filteredByRelevance;
    }


}
