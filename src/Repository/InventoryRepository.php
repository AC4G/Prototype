<?php

namespace App\Repository;

use App\Entity\Inventory;
use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Inventory::class
        );
    }

    public function findWithFilter(
        User $user,
        ?string $amount,
        ?string $projectName,
        ?User $creator
    ): array
    {
        $queryBuilder = $this->createQueryBuilder(alias: 'inv')
            ->join('inv.item','item')
            ->andWhere('inv.user = :user')
            ->setParameter('user', $user);
        if (!is_null($amount)) {
            $queryBuilder->andWhere('inv.amount = :amount')
                ->setParameter('amount', intval($amount));
        }
        if (!is_null($projectName)) {
            $queryBuilder->join('item.project', 'proj')
                ->andWhere('proj.project_name = :projectName')
                ->setParameter('projectName', $projectName);
        }
        if (!is_null($creator)) {
            $queryBuilder->andWhere('item.user = :creator')
                ->setParameter('creator', $creator);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
