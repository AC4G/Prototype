<?php

namespace App\Repository;

use App\Entity\UserPublicKey;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserPublicKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserPublicKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserPublicKey[]    findAll()
 * @method UserPublicKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserPublicKeyRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, UserPublicKey::class
        );
    }
}
