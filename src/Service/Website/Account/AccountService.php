<?php declare(strict_types=1);

namespace App\Service\Website\Account;

use DateTime;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

final class AccountService
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserRepository $userRepository
    )
    {
    }

    public function saveProfilePicture(
        UploadedFile $file,
        UserInterface $user
    ): UserInterface
    {
        $nickname = $user->getNickname();
        $dir = 'files/profile/' . $nickname . '/';
        $extension = $this->getFileExtension($file);
        $newFileName = $nickname . '.' . $extension;

        $this->deleteProfilePicture($user->getProfilePic(), $newFileName);

        $this->createUserProfileFolder($dir, $nickname);

        $file->move($dir, $newFileName);

        $user->setProfilePic($dir . $newFileName);
        $this->userRepository->flushEntity();

        return $user;
    }

    private function getFileExtension(
        UploadedFile $file
    ): string
    {
        return substr($file->getClientOriginalName(), strpos($file->getClientOriginalName(), '.') + 1);
    }

    private function createUserProfileFolder(
        string $dir,
        string $nickname
    ): void
    {
        if (!file_exists($dir)) {
            mkdir('files/profile/' . $nickname);
        }
    }

    private function deleteProfilePicture(
        ?string $oldProfilePicture,
        string $newFileName
    ): void
    {
        if (is_null($oldProfilePicture) || !$this->fileExists($oldProfilePicture)) {
            return;
        }

        if ($this->newFileNameMatchesOldOne($oldProfilePicture, $newFileName)) {
            return;
        }

        unlink($oldProfilePicture);
    }

    private function newFileNameMatchesOldOne(
        string $oldProfilePicture,
        string $newFileName
    ): bool
    {
        return strpos($oldProfilePicture, $newFileName) > 0;
    }

    private function fileExists(
        ?string $oldProfilePicture
    ): bool
    {
        return file_exists($oldProfilePicture);
    }

    public function updateNickname(
        string $nickname,
        UserInterface $user
    ): void
    {
        if (!is_null($this->userRepository->findOneBy(['nickname' => $nickname]))) {
            return;
        }

        $oldNickname = $user->getNickname();

        $newPath = $this->renameFolderAndReturnNewPath($oldNickname, $nickname);

        $user
            ->setNickname($nickname)
            ->setProfilePic($newPath)
        ;

        $this->userRepository->flushEntity();
    }

    private function renameFolderAndReturnNewPath(
        string $oldNickname,
        string $nickname
    ): null|string
    {
        $path = 'files/profile/';

        if (!is_dir($path .  $oldNickname)) {
            return null;
        }

        $extension = pathinfo(scandir($path . $oldNickname)[2])['extension'];

        if (strlen($extension) > 0) {
            rename($path . $oldNickname . '/' . $oldNickname . '.' . $extension, $path .  $oldNickname . '/' . $nickname . '.' . $extension);
        }

        rename($path .  $oldNickname . '/', $path . $nickname . '/');

        return $path . $nickname . '/' . $nickname . '.' . $extension;
    }

    public function updateProfilePrivacy(
        bool $privacy,
        UserInterface $user
    ): void
    {
        $user->setIsPrivate($privacy);

        $this->userRepository->flushEntity();
    }

    public function updateEmail(
        string $email,
        UserInterface $user
    ): void
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
    ): void
    {
        if (password_verify($password, $user->getPassword())) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->flushEntity();
    }

    public function updateTwoStepSecret(
        UserInterface $user
    ): void
    {
        $secret = $this->googleAuthenticator->generateSecret();

        $user->setGoogleAuthenticatorSecret($secret);

        $this->userRepository->flushEntity();
    }

    public function disableTwoStepVerification(
        User $user
    ): void
    {
        $user
            ->setGoogleAuthenticatorSecret(null)
            ->setTwoFaVerified(null);
        ;

        $this->userRepository->flushEntity();
    }

    public function setTwofaVerified(
        User $user
    ): void
    {
        $user->setTwoFaVerified(new DateTime());

        $this->userRepository->flushEntity();
    }

    public function isTwofaValid(
        User $user,
        string $code
    ): bool
    {
        return $this->googleAuthenticator->checkCode($user, $code);
    }


}

