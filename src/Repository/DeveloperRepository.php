<?php

namespace App\Repository;

use App\Entity\Developer;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Developer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Developer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Developer[]    findAll()
 * @method Developer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeveloperRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Developer::class
        );
    }
}
