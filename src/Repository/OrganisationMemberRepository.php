<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\OrganisationMember;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method OrganisationMember|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrganisationMember|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrganisationMember[]    findAll()
 * @method OrganisationMember[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrganisationMemberRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, OrganisationMember::class
        );
    }

    public function getOrganisationsByMemberFromCache(
        User $user
    ): array
    {
        return $this->cache->get('organisations_by_member_' . $user->getUuid(), function (ItemInterface $item) use ($user) {
            $item->expiresAfter(86400);

            return $this->findBy(['user' => $user]);
        });
    }

    public function getOrganisationsByMemberAndQuery(
        User $user,
        string $query
    ): array
    {
        if (strlen($query) === 0) {
            return $this->getOrganisationsByMemberFromCache($user);
        }

        if (strlen($query) <= 4) {
            $qb = $this->createQueryBuilder('om')
                ->select('om', 'o')
                ->where('om.user = :user')
                ->join('om.organisation', 'o')
                ->andWhere('o.name LIKE :query')
                ->setParameter('user', $user)
                ->setParameter('query', '%' . $query . '%')
                ->getQuery();

            return $qb->getArrayResult();
        }

        $qb = $this->createQueryBuilder('om')
            ->select('om', 'o')
            ->where('om.user = :user')
            ->join('om.organisation', 'o')
            ->andWhere('MATCH (o.name) AGAINST (:query IN BOOLEAN MODE) > 0')
            ->setParameter('user', $user)
            ->setParameter('query', $query)
            ->getQuery();

        return $qb->getArrayResult();
    }


}
