<?php declare(strict_types=1);

namespace App\Service\Website\Registration;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Token;
use App\Entity\UserRoles;
use App\Entity\RoleIdent;
use App\Repository\UserRepository;
use App\Repository\TokenRepository;
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
    private Token $registrationToken;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private RoleIdentRepository $roleIdentRepository,
        private UserRolesRepository $userRolesRepository,
        private TokenRepository $tokenRepository,
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

        try {
            $this->userRepository->persistAndFlushEntity($user);
        } catch (Exception $e) {
            $this->errors[] = $this->createError('saving', 'user_entity');
        }

        $this->user = $user;
    }

    public function giveUserARole(
        User $user
    ): void
    {
        $roleIdent = new RoleIdent();
        $roleIdent
            ->setRoles(json_encode(['ROLE_USER']));

        try {
            $this->roleIdentRepository->persistEntity($roleIdent);
        } catch (Exception $e) {
            $this->errors[] = $this->createError('saving', 'roleIdent_entity');
        }

        if (count($this->errors) < 1) {
            $userRoles = new UserRoles();

            $userRoles
                ->setUser($user)
                ->setRoleIdent($roleIdent)
            ;

            try {
                $this->userRolesRepository->persistEntity($userRoles);
            } catch (Exception $e) {
                    $this->errors[] = $this->createError('saving', 'userRoles_entity');
            }
        }
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

    public function flushToken()
    {
        $this->tokenRepository->flushEntity();
    }
}
