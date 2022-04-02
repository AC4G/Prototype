<?php

namespace App\Repository;

use App\Entity\RoleIdent;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RoleIdent|null find($id, $lockMode = null, $lockVersion = null)
 * @method RoleIdent|null findOneBy(array $criteria, array $orderBy = null)
 * @method RoleIdent[]    findAll()
 * @method RoleIdent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleIdentRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, RoleIdent::class
        );
    }
}
