<?php declare(strict_types=1);

namespace App\Service\Website\Registration;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\UserRoles;
use App\Entity\RoleIdent;
use App\Repository\UserRepository;
use App\Entity\UserRegistrationKey;
use App\Repository\RoleIdentRepository;
use App\Repository\UserRolesRepository;
use App\Repository\UserRegistrationKeyRepository as KeyRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationService
{
    private User $user;
    private array $errors = [];
    private array $errorSet = [
        'saving' => 'An error occurred while saving, please try again in a few seconds.'
    ];
    private UserRegistrationKey $registrationKey;

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private RoleIdentRepository $roleIdentRepository,
        private UserRolesRepository $userRolesRepository,
        private UserRepository $userRepository,
        private KeyRepository $keyRepository
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

        if (!is_null($this->user->getId())) {
            $this->giveUserAVerificationKey($user);
        }
    }

    public function giveUserARole(
        User $user
    ): void
    {
        $roleIdent = new RoleIdent();
        $roleIdent
            ->setRoleName(json_encode(['ROLE_USER']));

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

    private function giveUserAVerificationKey(
        User $user
    )
    {
        $key = bin2hex(random_bytes(64));

        $userRegistrationKey = new UserRegistrationKey();

        $userRegistrationKey
            ->setUser($user)
            ->setKey($key)
            ->setExpireDate(new DateTime('+ 10 Days'));

        try {
            $this->keyRepository->persistEntity($userRegistrationKey);
        } catch (Exception $e) {
            $this->errors[] = $this->createError('saving', 'registrationKey_entity');
        }

        $this->registrationKey = $userRegistrationKey;
    }

    private function createError(
        string $key,
        string $area
    ): string
    {
        return $this->errorSet[$key] . ' Area: ' . $area;
    }

    public function getUserRegistrationKey(): ?UserRegistrationKey
    {
        return $this->registrationKey;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function flushUserRegistrationKey()
    {
        $this->keyRepository->flushEntity();
    }
}
