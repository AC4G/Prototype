<?php declare(strict_types=1);

namespace App\Service\Website\Account;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AccountService
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher,
        private UserRepository $userRepository
    )
    {
    }

    public function saveProfilePicture(
        UploadedFile $file,
        UserInterface $user
    )
    {
        $extension = substr($file->getClientOriginalName(), strpos($file->getClientOriginalName(), '.') + 1);

        $nickname = strtolower($user->getNickname());
        $dir = 'files/profile/' . $nickname . '/';

        if (file_exists($dir) && count(scandir($dir)) > 0) {
            $files = glob($dir . '*', GLOB_MARK);
            foreach ($files as $fileOld) {
                if (is_file($fileOld)) {
                    unlink($fileOld);
                }
            }
        }
        if (!file_exists($dir)) {
            mkdir('files/profile/' . $nickname);
        }

        $newFileName = $nickname . '.' . $extension;

        $file->move($dir, $newFileName);

        $user->setProfilePic($dir . $newFileName);
        $this->userRepository->flushEntity();

        sleep(1);
    }

    public function updateProfilePrivacy(
        bool $privacy,
        UserInterface $user
    )
    {
        $user->setIsPrivate($privacy);

        $this->userRepository->flushEntity();
    }

    public function updateNickname(
        string $nickname,
        UserInterface $user
    )
    {
        if (!is_null($this->userRepository->findOneBy(['nickname' => $nickname]))) {
            return;
        }

        $user->setNickname($nickname);

        $this->userRepository->flushEntity();
    }

    public function updateEmail(
        string $email,
        UserInterface $user
    )
    {
        if (!is_null($this->userRepository->findOneBy(['email' => $email]))) {
            return;
        }

        $user->setEmail($email);

        $this->userRepository->flushEntity();
    }

    public function updatePassword(
        string $password,
        UserInterface $user
    )
    {
        if (password_verify($password, $user->getPassword())) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->flushEntity();
    }


}

