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

    public function updateEntries(
        UserInterface $user,
        Request $request
    ): void
    {
        $currentForm = $request->request->get('form-type');

        if ($this->isFormType($currentForm, 'picture')) {
            $this->updatePicture($user, $request);
        }

        if ($this->isFormType($currentForm, 'privacy')) {
            $this->updatePrivacy($user, $request);
        }

        if ($this->isFormType($currentForm, 'nickname')) {
            $this->updateNickname($user, $request);
        }

        if ($this->isFormType($currentForm, 'email')) {
            $this->updateEmail($user, $request);
        }

        if ($this->isFormType($currentForm, 'password')) {
            $this->updatePassword($user, $request);
        }
    }

    private function isFormType(
        string $currentForm,
        string $neededForm
    ): bool
    {
        return $currentForm === $neededForm;
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
        $email = $request->request->get('email');

        if (!is_null($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->accountService->updateEmail($email, $user);
        }
    }


}
