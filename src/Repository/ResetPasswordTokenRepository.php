<?php

namespace App\Repository;

use App\Entity\ResetPasswordToken;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResetPasswordToken|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResetPasswordToken|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResetPasswordToken[]    findAll()
 * @method ResetPasswordToken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResetPasswordTokenRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ResetPasswordToken::class
        );
    }
}
