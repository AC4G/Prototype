<?php declare(strict_types=1);

namespace App\Service\Website\Dashboard;

use App\Entity\User;
use chillerlan\QRCode\QRCode;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Website\Account\AccountService;
use Symfony\Component\Security\Core\User\UserInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

final class ProfileService
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly AccountService $accountService
    )
    {
    }

    public function updateProfile(
        UserInterface $user,
        Request $request
    ): void
    {
        $currentFormName = $request->request->get('form');

        $functionName = 'update' . ucfirst($currentFormName);

        if (method_exists(ProfileService::class, $functionName)) {
            $this->$functionName($user, $request);
        }
    }

    private function updatePicture(
        UserInterface $user,
        Request $request
    ): void
    {
        $file = $request->files->get('profile-picture');
        $types = [
            'image/gif',
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/jfif'
        ];

        if (!is_null($file) && in_array($file->getMimeType(), $types)) {
            $this->accountService->saveProfilePicture($file, $user);

            sleep(2);
        }
    }

    private function updatePrivacy(
        UserInterface $user,
        Request $request
    ): void
    {
        $privacy = $request->request->get('privacy');

        if ($privacy === '1') {
            $privacy = true;
        }

        if ($privacy === '0') {
            $privacy = false;
        }

        $this->accountService->updateProfilePrivacy($privacy, $user);
    }

    private function updateNickname(
        UserInterface $user,
        Request $request
    ): void
    {
        $nickname = $request->request->get('nickname');

        if (!is_null($nickname) && strlen($nickname) > 0) {
            $this->accountService->updateNickname($nickname, $user);
        }
    }

    private function updateEmail(
        UserInterface $user,
        Request $request
    ): void
    {
        $email = $request->request->get('email');

        if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->accountService->updateEmail($email, $user);
        }
    }

    public function updatePassword(
        UserInterface $user,
        Request $request
    ): void
    {
        $password1 = $request->request->get('password-1');
        $password2 = $request->request->get('password-2');

        if ((!is_null($password1) && !is_null($password2)) && $password1 === $password2 && strlen($password2) >= 10) {
            $this->accountService->updatePassword($password2, $user);
        }
    }

    public function generateTwoStepVerificationQRCode(
        TwoFactorInterface $user
    ): string
    {
        $qrCode = new QRCode();

        return $qrCode->render($this->googleAuthenticator->getQRContent($user));
    }

    public function isTwofaCodeValid(
        User $user,
        ?string $code
    ): bool
    {
        if (is_null($code)) {
            return false;
        }

        return $this->accountService->isTwofaValid($user, $code);
    }

    public function disableTwoStepVerification(
        User $user
    ): void
    {
        $this->accountService->disableTwoStepVerification($user);
    }

    public function verifyTwoStepSecret(
        User $user
    ): void
    {
        $this->accountService->setTwofaVerified($user);
    }


}
