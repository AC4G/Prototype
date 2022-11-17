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
        string $phrase,
        ?UserInterface $user = null
    ): array
    {
        return $this->buildQuery($phrase, $user)->execute();
    }

    private function buildQuery(
        string $phrase,
        ?UserInterface $user = null
    ): null|Query
    {
        if (!is_null($user)) {
            return $this->itemRepository->createQueryBuilder('i')
                ->where('i.user = :user')
                ->andWhere('MATCH (i.name) AGAINST (:phrase IN NATURAL LANGUAGE MODE)')
                ->setParameter('user', $user)
                ->setParameter('phrase', $phrase)
                ->getQuery()
            ;
        }

        return $this->itemRepository->createQueryBuilder('i')
            ->where('i.name LIKE :phrase')
            ->setParameter('phrase', '%' . $phrase . '%')
            ->getQuery()
        ;
    }
}