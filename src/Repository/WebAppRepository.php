<?php

namespace App\Repository;

use App\Entity\WebApp;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WebApp|null find($id, $lockMode = null, $lockVersion = null)
 * @method WebApp|null findOneBy(array $criteria, array $orderBy = null)
 * @method WebApp[]    findAll()
 * @method WebApp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WebAppRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, WebApp::class
        );
    }
}
