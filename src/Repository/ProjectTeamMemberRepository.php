<?php

namespace App\Repository;

use App\Entity\ProjectTeamMember;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProjectTeamMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProjectTeamMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProjectTeamMember[]    findAll()
 * @method ProjectTeamMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectTeamMemberRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, ProjectTeamMember::class
        );
    }


}