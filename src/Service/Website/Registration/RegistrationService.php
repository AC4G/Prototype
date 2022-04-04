<?php declare(strict_types=1);

namespace App\Service\Website\Registration;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\UserRoles;
use App\Entity\RoleIdent;
use App\Repository\UserRepository;
use App\Entity\Token;
use App\Repository\RoleIdentRepository;
use App\Repository\UserRolesRepository;
use App\Repository\TokenRepository;
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
        private UserRepository $userRepository,
        private TokenRepository $tokenRepository
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
            ->setRoles(json_encode('ROLE_USER'));

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

        $token = new Token();

        $token
            ->setUser($user)
            ->setToken($key)
            ->setType("email_verification")
            ->setExpireDate(new DateTime('+ 10 Days'));

        try {
            $this->tokenRepository->persistEntity($token);
        } catch (Exception $e) {
            $this->errors[] = $this->createError('saving', 'registrationKey_entity');
        }

        $this->registrationToken = $token;
    }

    private function createError(
        string $key,
        string $area
    ): string
    {
        return $this->errorSet[$key] . ' Area: ' . $area;
    }

    public function getToken(): ?Token
    {
        return $this->registrationToken;
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
