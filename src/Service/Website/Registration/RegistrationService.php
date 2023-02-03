<?php declare(strict_types=1);

namespace App\Service\Website\Registration;

use DateTime;
use App\Entity\User;
use Ramsey\Uuid\Uuid;
use App\Entity\UserRoles;
use App\Entity\RoleIdent;
use App\Repository\UserRepository;
use App\Repository\RoleIdentRepository;
use App\Repository\UserRolesRepository;
use App\Service\Website\Email\EmailService;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegistrationService
{
    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly RoleIdentRepository $roleIdentRepository,
        private readonly UserRolesRepository $userRolesRepository,
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService
    )
    {
    }

    public function registerUser(
        User $user,
        array $data
    ): User
    {
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            $data['password']['first']
        );

        $user = $this->persistUser($user, $data, $hashedPassword);

        $this->assignRoleToUser($user, 'ROLE_USER');

        $this->buildAndSendVerificationEmail($user);

        return $user;
    }

    private function assignRoleToUser(
        User $user,
        string $roleName
    ): void
    {
        $roleIdent = $this->roleIdentRepository->findOneBy(['roleName' => $roleName]);

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
    }

    private function persistUser(
        User $user,
        array $data,
        string $hashedPassword
    ): User
    {
        $user
            ->setUuid(Uuid::uuid4()->toString())
            ->setNickName($data['nickname'])
            ->setEmail($data['email'])
            ->setPassword($hashedPassword)
            ->setCreationDate(new DateTime())
        ;

        $this->userRepository->persistAndFlushEntity($user);

        return $user;
    }

    private function buildAndSendVerificationEmail(
        User $user
    ): void
    {
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'verify_email',
            (string)$user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );
        $this->emailService->createEmail(
            $user->getEmail(),
            'Email verification',
            'website/email/registration/index.html.twig',
            ['verify_url' => $signatureComponents->getSignedUrl()]
        );
        $this->emailService->sendEmail();
    }

    public function getValidationErrors(
        array $data
    ): array
    {
        $errors = [];

        if($this->userRepository->isNicknameAlreadyUsed($data['nickname'])) {
            $errors[] =  "Nickname with the same characters is already in use.";
        }

        if ($this->userRepository->isEmailAlreadyUsed($data['email'])) {
            $errors[] = 'The specified email is invalid.';
        }

        return $errors;
    }

    public function setEmailVerifiedDate(
        User $user
    ): void
    {
        $user->setEmailVerified(new DateTime());
        $this->userRepository->flushEntity();
    }


}
