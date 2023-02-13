<?php declare(strict_types=1);

namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Repository\UserRepository;
use App\Repository\UserRolesRepository;
use App\Repository\RoleIdentRepository;
use App\Service\Website\Email\EmailService;
use App\Service\Website\Registration\RegistrationService;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationServiceTest extends TestCase
{
    public function testGetValidationErrors()
    {
        $verifyEmailHelper = $this->createMock(VerifyEmailHelperInterface::class);
        $passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $roleIdentRepository = $this->createMock(RoleIdentRepository::class);
        $userRolesRepository = $this->createMock(UserRolesRepository::class);
        $userRepository = $this->createMock(UserRepository::class);
        $emailService = $this->createMock(EmailService::class);

        $userRepository->expects($this->once())
            ->method('isNicknameAlreadyUsed')
            ->with('testnickname')
            ->willReturn(true);

        $userRepository->expects($this->once())
            ->method('isEmailAlreadyUsed')
            ->with('Tests@example.com')
            ->willReturn(false);

        $classUnderTest = new RegistrationService(
            $verifyEmailHelper,
            $passwordHasher,
            $roleIdentRepository,
            $userRolesRepository,
            $userRepository,
            $emailService
        );

        $errors = $classUnderTest->getValidationErrors([
            'nickname' => 'testnickname',
            'email' => 'Tests@example.com'
        ]);

        $this->assertContains('Nickname with the same characters is already in use.', $errors);

        $this->assertNotContains('The specified email is invalid.', $errors);
    }




}
