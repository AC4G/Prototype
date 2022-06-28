<?php declare(strict_types=1);

namespace App\Service\Website\Registration;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\UserRoles;
use App\Entity\RoleIdent;
use App\Repository\UserRepository;
use App\Repository\RoleIdentRepository;
use App\Repository\UserRolesRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationService
{
    private User $user;
    private array $errors = [];
    private array $errorSet = [
        'saving' => 'An error occurred while saving, please try again in a few seconds.'
    ];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private RoleIdentRepository $roleIdentRepository,
        private UserRolesRepository $userRolesRepository,
        private UserRepository $userRepository
    )
    {
    }

    public function registerUser(
        User $user
    ): void
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $user->getPassword()
        );

        $user
            ->setNickName($user->getNickname())
            ->setEmail($user->getEmail())
            ->setPassword($hashedPassword)
            ->setCreationDate(new DateTime())
        ;

        $this->userRepository->persistAndFlushEntity($user);

        $roleIdent = $this->roleIdentRepository->findOneBy(['roleName' => 'ROLE_USER']);

        if (is_null($roleIdent)) {
            $roleIdent = new RoleIdent();
            $roleIdent
                ->setRoleName('ROLE_USER');
        }

        $this->roleIdentRepository->persistAndFlushEntity($roleIdent);

        $userRoles = new UserRoles();

        $userRoles
            ->setUser($user)
            ->setRoleIdent($roleIdent)
        ;

        $this->userRolesRepository->persistAndFlushEntity($userRoles);

        $this->user = $user;
    }

    private function createError(
        string $key,
        string $area
    ): string
    {
        return $this->errorSet[$key] . ' Area: ' . $area;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getUser(): User
    {
        return $this->user;
    }


}
