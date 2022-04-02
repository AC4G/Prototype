<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends AbstractRepository
{
    public function __construct(
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

    public function userExists(int $id): bool
    {
        return !is_null($this->findOneBy(['id' => $id]));
    }
}
