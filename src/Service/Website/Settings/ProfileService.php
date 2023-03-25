<?php declare(strict_types=1);

namespace App\Service\Website\Settings;

use App\Entity\User;
use App\Entity\UserToken;
use App\Repository\UserTokenRepository;
use App\Service\Website\Account\AccountService;
use chillerlan\QRCode\QRCode;
use DateTime;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;

final class ProfileService
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly UserTokenRepository $userTokenRepository,
        private readonly AccountService $accountService
    )
    {
    }

    public function updateProfile(
        User $user,
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
        User $user,
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
        User $user,
        Request $request
    ): void
    {
        $privacy = (bool)$request->request->get('privacy');

        $this->accountService->updateProfilePrivacy($privacy, $user);
    }

    private function updateNickname(
        User $user,
        Request $request
    ): void
    {
        $nickname = $request->request->get('nickname');

        if (!is_null($nickname) && strlen($nickname) > 0) {
            $this->accountService->updateNickname($nickname, $user);
        }
    }

    private function updateEmail(
        User $user,
        Request $request
    ): void
    {
        $email = $request->request->get('email');

        if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->accountService->updateEmail($email, $user);
        }
    }

    public function updatePassword(
        User $user,
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

    public function generateTwoStepVerificationOneTimeTokens(
        User $user
    ): array
    {
        $existingTokens = $this->userTokenRepository->findBy(['user' => $user, 'type' => '2fa-one-time']);

        if (count($existingTokens) > 0) {
            return $existingTokens;
        }

        $tokens = [];

        for ($i = 0; $i < 10; $i++) {
            $tokens[$i] = (new UserToken())
                ->setUser($user)
                ->setToken(bin2hex(random_bytes(8)))
                ->setType('2fa-one-time')
                ->setCreationDate(new DateTime())
            ;

            $this->userTokenRepository->persistAndFlushEntity($tokens[$i]);
        }

        return $tokens;
    }

    public function isTwoFaCodeValid(
        User $user,
        ?string $code
    ): bool
    {
        if (is_null($code)) {
            return false;
        }

        return $this->accountService->isTwoFaValid($user, $code);
    }

    public function verifyTwoStepSecret(
        User $user
    ): void
    {
        $this->accountService->setTwoFaVerified($user);
    }

    public function removeTokensAndUnsetTwoFa(
        User $user
    ): void
    {
        $this->accountService->unsetTwoStepVerification($user);
        $this->accountService->removeTwoFaOneTimeTokens($user);
    }


}
