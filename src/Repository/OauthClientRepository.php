<?php

namespace App\Repository;

use App\Entity\OauthClient;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OauthClient|null find($id, $lockMode = null, $lockVersion = null)
 * @method OauthClient|null findOneBy(array $criteria, array $orderBy = null)
 * @method OauthClient[]    findAll()
 * @method OauthClient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OauthClientRepository extends AbstractRepository
{
    public function __construct(
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, OauthClient::class
        );
    }
}
