<?php

namespace App\Repository;

use App\Entity\Scope;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Scope|null find($id, $lockMode = null, $lockVersion = null)
 * @method Scope|null findOneBy(array $criteria, array $orderBy = null)
 * @method Scope[]    findAll()
 * @method Scope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScopeRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Scope::class
        );
    }
}
