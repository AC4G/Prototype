<?php declare(strict_types=1);

namespace App\Engine\Search;

use Doctrine\ORM\Query;
use App\Repository\ItemRepository;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemSearchEngine
{
    public function __construct(
        private ItemRepository $itemRepository
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

        return $this->buildQuery($phrase, $user)->execute();
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
                    goto a;
                }

                continue;
            }

            foreach ($parameters as $parameter) {
                if (strpos($item->getParameter(), $parameter) > 0 && !array_key_exists($item->getId(), $foundContent)) {
                    $foundContent[$key] = $item;
                }
            }
        }

        a:

        return $foundContent;
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