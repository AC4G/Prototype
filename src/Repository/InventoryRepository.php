<?php

namespace App\Repository;

use App\Entity\Inventory;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inventory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventory[]    findAll()
 * @method Inventory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventoryRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, Inventory::class
        );
    }


}
