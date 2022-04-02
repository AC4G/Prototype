<?php

namespace App\Repository;

use App\Entity\ChatRoomType;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatRoomType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatRoomType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatRoomType[]    findAll()
 * @method ChatRoomType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRoomTypeRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ChatRoomType::class
        );
    }
}
