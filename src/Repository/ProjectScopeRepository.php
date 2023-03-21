<?php

namespace App\Repository;

use App\Entity\ProjectScope;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectScope|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectScope|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectScope[]    findAll()
 * @method ProjectScope[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectScopeRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ProjectScope::class
        );
    }
}
