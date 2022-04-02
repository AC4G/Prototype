<?php

namespace App\Repository;

use App\Entity\ChatRoomMember;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ChatRoomMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method ChatRoomMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method ChatRoomMember[]    findAll()
 * @method ChatRoomMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChatRoomMemberRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ChatRoomMember::class
        );
    }
}
