<?php

namespace App\Repository;

use App\Entity\UserRoles;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserRoles|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRoles|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRoles[]    findAll()
 * @method UserRoles[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRolesRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, UserRoles::class
        );
    }
}
