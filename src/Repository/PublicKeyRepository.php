<?php

namespace App\Repository;

use App\Entity\PublicKey;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PublicKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublicKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublicKey[]    findAll()
 * @method PublicKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublicKeyRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, PublicKey::class
        );
    }
}
