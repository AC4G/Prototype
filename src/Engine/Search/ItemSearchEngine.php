<?php declare(strict_types=1);

namespace App\Engine\Search;

use Doctrine\ORM\Query;
use App\Repository\ItemRepository;
use App\Service\API\Items\ItemsService;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemSearchEngine extends AbstractSearchEngine
{
    public function __construct(
        private ItemRepository $itemRepository,
        private ItemsService $itemsService
    )
    {
        parent::__construct(
            $this->itemsService
        );
    }

    public function search(
        ?string $phrase,
        ?UserInterface $user = null
    ): array
    {
        if (is_null($phrase) || strlen($phrase) === 0) return $this->itemRepository->findBy(['user' => $user]);

        if (strpos($phrase, ':') > 0) {
            $phraseWithParameter = explode(':', $phrase);

            if (strlen($phraseWithParameter[1]) === 0) goto a;

            $parameter = $this->preparePhraseParameter($phraseWithParameter[1]);

            $items = $this->buildQuery($phraseWithParameter[0], $user)->execute();

            return $this->findItemByParameter($items, $parameter);
        }

        a:

        return $this->itemsService->prepareData($this->buildQuery($phrase, $user)->execute());
    }

    private function buildQuery(
        string $phrase,
        ?UserInterface $user = null,
        array $context = [],
    ): Query
    {
        $query = $this->itemRepository->createQueryBuilder('i')
                ->where('MATCH (i.name) AGAINST (:phrase IN BOOLEAN MODE) > 0')
                ->setParameter('phrase', (count(explode(' ', $phrase)) === 1) ? '\'' . $phrase . '*\'' : '"' . $phrase . '"')
        ;

        if (is_null($user))
            $query
                ->andWhere('i.user = :user')
                ->setParameter('user', $user)
        ;

        return $query->getQuery();
    }


}
