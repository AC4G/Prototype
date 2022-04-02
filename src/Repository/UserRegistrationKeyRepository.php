<?php

namespace App\Repository;

use App\Entity\UserRegistrationKey;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserRegistrationKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserRegistrationKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserRegistrationKey[]    findAll()
 * @method UserRegistrationKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRegistrationKeyRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, UserRegistrationKey::class
        );
    }
}
