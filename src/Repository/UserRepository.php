<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository
{
    public function __construct(
        private readonly CacheInterface $cache,
        ManagerRegistry $registry
    )
    {
        parent::__construct(
            $registry, User::class
        );
    }

    public function isNicknameAlreadyUsed(string $nickname): bool
    {
        return !is_null($this->findOneBy(['nickname' => $nickname]));
    }

    public function isEmailAlreadyUsed(string $email): bool
    {

        return !is_null($this->findOneBy(['email' => $email]));
    }

    public function getUserByUuidFromCache(
        string $uuid
    ): null|User
    {
        return $this->cache->get('user_'. $uuid, function (ItemInterface $item) use ($uuid) {
            $item->expiresAfter(86400);

            return $this->findOneBy(['uuid' => $uuid]);
        });
    }

    public function getUserByUuidOrNicknameFromCache(
        string $identifier
    ): null|User
    {
        return $this->cache->get('user_'. $identifier, function (ItemInterface $item) use ($identifier) {
            $item->expiresAfter(86400);

            return $this->matching(
                Criteria::create()
                    ->where(Criteria::expr()->eq('uuid', $identifier))
                    ->orWhere(Criteria::expr()->eq('nickname', $identifier))
            )->get(0);
        });
    }


}
