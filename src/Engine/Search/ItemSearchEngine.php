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
        $query = $this->itemRepository->createQueryBuilder('i')
                ->where('MATCH (i.name) AGAINST (:phrase) > 0')
                ->setParameter('phrase', '%' . $phrase . '%')
        ;

        !is_null($user) ??
            $query
                ->andWhere('i.user = :user')
                ->setParameter('user', $user)
        ;

        return $query->getQuery();
    }
}