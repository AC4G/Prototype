<?php declare(strict_types=1);

namespace App\Service\Website\Dashboard;

use Symfony\Component\HttpFoundation\Request;
use App\Service\Website\Account\AccountService;
use Symfony\Component\Security\Core\User\UserInterface;

final class ProfileService
{
    public function __construct(
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


}
