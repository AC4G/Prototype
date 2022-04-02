<?php

namespace App\Repository;

use App\Entity\ChatRoom;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatRoom|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatRoom|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatRoom[]    findAll()
 * @method ChatRoom[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRoomRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ChatRoom::class
        );
    }
}
