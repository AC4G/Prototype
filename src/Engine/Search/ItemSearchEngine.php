<?php declare(strict_types=1);

namespace App\Engine\Search;

use Doctrine\ORM\Query;
use App\Repository\ItemRepository;
use App\Service\API\Items\ItemsService;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemSearchEngine
{
    private array $amountOfOccurrence = [];

    public function __construct(
        private ItemRepository $itemRepository,
        private ItemsService $itemsService
    )
    {
    }

    public function search(
        ?string $phrase,
        ?UserInterface $user = null
    ): array
    {
        if (is_null($phrase) || strlen($phrase) === 0) return $this->itemRepository->findBy(['user' => $user]);

        if (strpos($phrase, ':') > 0) {
            $phraseWithParameter = explode(':', $phrase);
            $parameter = $this->preparePhraseParameter($phraseWithParameter[1]);

            $items = $this->buildQuery($phraseWithParameter[0], $user)->execute();

            return $this->findItemByParameter($items, $parameter);
        }

        return $this->itemsService->prepareData($this->buildQuery($phrase, $user)->execute());
    }

    private function preparePhraseParameter(
        string $parameter
    ): array|string
    {
        if (strpos($parameter, ',') > 0) {
            return array_map('trim',explode(',', $parameter));
        }

        return $parameter;
    }

    private function findItemByParameter(
        array $items,
        array|string $parameters
    ): array
    {
        $foundContent = [];

        foreach ($items as $key => $item) {
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
            return $this->itemsService->prepareData($foundContent);
        }

        return $this->filterContentByOccurrenceOfAllPhrases($foundContent, count($parameters));
    }

    //relevance of the searched items
    private function filterContentByOccurrenceOfAllPhrases(
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
                $normalizedItem = $this->itemsService->prepareData($foundContent[$key]);
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

        return $filteredByRelevance;
    }

    private function buildQuery(
        string $phrase,
        ?UserInterface $user = null
    ): Query
    {
        $query = $this->itemRepository->createQueryBuilder('i')
                ->where('MATCH (i.name) AGAINST (:phrase IN BOOLEAN MODE) > 0')
                ->setParameter('phrase', (count(explode(' ', $phrase)) === 1) ? '\'' . $phrase . '*\'' : '"' . $phrase . '"')
        ;

        !is_null($user) ??
            $query
                ->andWhere('i.user = :user')
                ->setParameter('user', $user)
        ;

        return $query->getQuery();
    }


}
