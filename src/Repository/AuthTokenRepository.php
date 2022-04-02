<?php

namespace App\Repository;

use App\Entity\AuthToken;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AuthToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method AuthToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method AuthToken[]    findAll()
 * @method AuthToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AuthTokenRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, AuthToken::class
        );
    }
}
