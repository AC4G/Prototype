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
    private int $id;
    private array $errors = [];
    private array $errorSet = [
        'saving' => 'An error occurred while saving, please try again in a few seconds.'
    ];
    private UserRegistrationKey $registrationKey;
    private UserRoles $userRoles;
    private RoleIdent $roleIdent;

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

        $this->id = $user->getId();

        if (!is_null($this->id)) {
            $this->id = $user->getId();
            $this->giveUserARole($user);
            $this->giveUserAVerificationKey($user);
        }
    }

    private function giveUserARole(
        User $user
    ): void
    {
        $roleIdent = new RoleIdent();
        $roleIdent
            ->setRoleName(json_encode(['ROLE_USER']));

        try {
            $this->roleIdentRepository->persistAndFlushEntity($roleIdent);
        } catch (Exception $e) {
            $this->errors[] = $this->createError('saving', 'roleIdent_entity');
        }

        $this->roleIdent = $roleIdent;

        if (count($this->errors) < 1) {
            $userRoles = new UserRoles();

            $userRoles
                ->setUser($user)
                ->setRoleIdent($roleIdent)
            ;

            try {
                $this->userRolesRepository->persistAndFlushEntity($userRoles);
            } catch (Exception $e) {
                    $this->errors[] = $this->createError('saving', 'userRoles_entity');
            }

            $this->userRoles = $userRoles;
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
            $this->keyRepository->persistAndFlushEntity($userRegistrationKey);
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

    public function getUserId(
        bool $returnString = false
    ): int|string
    {
        if ($returnString === true) {
            return (string)$this->id;
        }

        return $this->id;
    }

    public function getUserRoles(): ?UserRoles
    {
        return $this->userRoles;
    }

    public function getRoleIdent(): ?RoleIdent
    {
        return $this->roleIdent;
    }
}
