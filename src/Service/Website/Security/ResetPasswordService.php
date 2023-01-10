<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Entity\ResetPasswordRequest;
use App\Service\Website\Email\EmailService;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\ResetPasswordRequestRepository;

class ResetPasswordService
{
    private User|null $user = null;

    public function __construct(
        private readonly ResetPasswordRequestRepository $resetPasswordRequestRepository,
        private readonly UserRepository $userRepository,
        private readonly EmailService $emailService
    )
    {
    }

    public function validateHandedEmail(
        ?array $data
    ): ?string
    {
        if (!is_array($data) || !array_key_exists('email', $data)) {
            return 'Invalid input';
        }

        $this->user = $this->userRepository->findOneBy(['email' => $data['email']]);

        if (is_null($this->user)) {
            return 'Email not found';
        }

        return null;
    }

    public function prepareForReset(
        Request $request
    )
    {
        $code = $this->generateCodeAndCreateEntry();
        $this->setEmailInToSession($request);
        $this->sendEmailWithCode($code);
    }

    private function generateCodeAndCreateEntry(): string
    {
        $resetPasswordRequest = new ResetPasswordRequest();
        $code = (string)mt_rand(000000, 999999);

        $resetPasswordRequest
            ->setUser($this->user)
            ->setCode($code)
            ->setCreatedOn(new DateTime())
            ->setExpiresOn(new DateTime('+ 5 minutes'))
        ;

        $this->resetPasswordRequestRepository->persistAndFlushEntity($resetPasswordRequest);

        return $code;
    }

    private function setEmailInToSession(
        Request $request
    )
    {
        $request->getSession()->set('reset_password_email', $this->user->getEmail());
    }

    private function sendEmailWithCode(
        string $code
    )
    {
        $this->emailService->createEmail(
            $this->user->getEmail(),
            'Reset password',
            'website/email/resetPassword/code.html.twig',
            ['code' => $code]
        );

        $this->emailService->sendEmail();
    }




}