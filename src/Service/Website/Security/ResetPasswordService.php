<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\User;
use App\Entity\ResetPasswordToken;
use App\Repository\UserRepository;
use App\Service\Website\Email\EmailService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ResetPasswordTokenRepository;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ResetPasswordService
{
    private string $hash;
    private User|null $user = null;
    private ResetPasswordToken|null $reset = null;

    public function __construct(
        private readonly ResetPasswordTokenRepository $resetPasswordTokenRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService
    )
    {
    }

    public function validateEmail(
        ?array $data
    ): ?string
    {
        if (!is_array($data) || !array_key_exists('email', $data)) {
            return 'Invalid input.';
        }

        $this->user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (is_null($this->user)) {
            return 'The specified email cannot be used for recovering.';
        }

        $this->reset = $this->resetPasswordTokenRepository->findOneBy(['user' => $this->user]);

        if (!is_null($this->reset)) {
            return 'Reset code already sent to this Email. Request new one?';
        }

        return null;
    }

    public function prepareForReset(
        Request $request
    ): void
    {
        $code = $this->generateCodeAndCreateEntry();
        $this->setEmailInToSession($request);
        $this->sendEmailWithCode($code);
    }

    private function generateCodeAndCreateEntry(): string
    {
        $resetPasswordToken = new ResetPasswordToken();
        $code = (string)mt_rand(000000, 999999);

        $resetPasswordToken
            ->setUser($this->user)
            ->setCode($code)
            ->setCreationDate(new DateTime())
            ->setExpireDate(new DateTime('+ 5 minutes'))
        ;

        $this->resetPasswordTokenRepository->persistAndFlushEntity($resetPasswordToken);

        return $code;
    }

    private function setEmailInToSession(
        Request $request
    ): void
    {
        $request->getSession()->set('reset_password_email', $this->user->getEmail());
    }

    private function sendEmailWithCode(
        string $code
    ): void
    {
        $this->emailService->createEmail(
            $this->user->getEmail(),
            'Reset password',
            'website/email/resetPassword/code.html.twig',
            ['code' => $code]
        );

        $this->emailService->sendEmail();
    }

    public function validateCode(
        Request $request,
        ?string $code
    ): null|string
    {
        if (is_null($code)) {
            return 'Incorrect request';
        }

        $email = $request->getSession()->get('reset_password_email');
        $this->user = $this->userRepository->findOneBy(['email' => $email]);
        $this->reset = $this->resetPasswordTokenRepository->findOneBy(['user' => $this->user, 'code' => $code]);

        if (is_null($this->reset)) {
            return 'Wrong code. Request new one!';
        }

        if (new DateTime() > $this->reset->getExpireDate()) {
            return 'Code expired. Request new one!';
        }

        return null;
    }

    public function setSessionIsVerifiedForResetPassword(
        Request $request
    ): void
    {
        $request->getSession()->set('is_verified_for_reset_password', true);
    }

    public function removeEntry(): void
    {
        $this->resetPasswordTokenRepository->deleteEntry($this->reset);
    }

    public function validatePassword(
        array $data,
        string $email
    ): null|string
    {
        if (count($data) === 0) {
            return null;
        }

        $password = $data['password'];

        if (strlen($password) < 10) {
            return 'Password is to short.';
        }

        $this->user = $this->userRepository->findOneBy(['email' => $email]);
        $this->hash = $this->passwordHasher->hashPassword($this->user, $data['password']);

        if ($this->hash === $this->user->getPassword()) {
            return 'Do not use your previous password.';
        }

        return null;
    }

    public function saveNewPassword(): void
    {
        $this->user->setPassword($this->hash);
        $this->userRepository->flushEntity();
    }

    public function setResetByUser(): void
    {
        $this->reset = $this->resetPasswordTokenRepository->findOneBy(['user' => $this->user]);
    }

    public function removeSessionsForResettingPassword(
        Request $request
    ): void
    {
        $request->getSession()->remove('reset_password_email');
        $request->getSession()->remove('is_verified_for_reset_password');
    }

    public function updateEntryAndSendEmail(): void
    {
        $code = (string)mt_rand(000000, 999999);

        $this->reset
            ->setCode($code)
            ->setCreationDate(new DateTime())
            ->setExpireDate(new DateTime('+ 5 minutes'))
        ;

        $this->resetPasswordTokenRepository->flushEntity();

        $this->sendEmailWithCode($code);
    }

    public function updateEntrySendEmailAndSetSession(
        Request $request
    ): void
    {
        $this->updateEntryAndSendEmail();
        $this->setEmailInToSession($request);
    }

    public function entryExists(
        string $email
    ): bool
    {
        $this->user = $this->userRepository->findOneBy(['email' => $email]);
        $reset = $this->resetPasswordTokenRepository->findOneBy(['user' => $this->user]);

        return !is_null($reset);
    }




}
