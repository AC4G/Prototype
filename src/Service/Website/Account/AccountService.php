<?php declare(strict_types=1);

namespace App\Service\Website\Account;

use DateTime;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\UserTokenRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

final class AccountService
{
    public function __construct(
        private readonly GoogleAuthenticatorInterface $googleAuthenticator,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly UserTokenRepository $userTokenRepository,
        private readonly UserRepository $userRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function saveProfilePicture(
        UploadedFile $file,
        User $user
    ): UserInterface
    {
        $nickname = $user->getNickname();
        $dir = '../assets/files/profile/' . $nickname . '/';
        $extension = $this->getFileExtension($file);
        $newFileName = $nickname . '.' . $extension;

        $this->deleteProfilePicture($user);

        $this->createUserProfileFolder($dir, $nickname);

        $file->move($dir, $newFileName);

        $user->setProfilePic('/files/profile/' . $user->getUuid());
        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);

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
            mkdir('../assets/files/profile/' . $nickname);
        }
    }

    private function deleteProfilePicture(
        User $user
    ): void
    {
        $files = glob('../assets/files/profile/' . $user->getNickname() . '/*');

        foreach ($files as $file) {
            if (is_file($file) && pathinfo($file)['filename'] === $user->getNickname()) {
                unlink($file);
                return;
            }
        }
    }

    public function updateNickname(
        string $nickname,
        User $user
    ): void
    {
        if (!is_null($this->userRepository->findOneBy(['nickname' => $nickname]))) {
            return;
        }

        $oldNickname = $user->getNickname();

        $newPath = '/' . $this->renameFolderAndReturnNewPath($oldNickname, $nickname);

        $user
            ->setNickname($nickname)
            ->setProfilePic($newPath)
        ;

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
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
        User $user
    ): void
    {
        $user->setIsPrivate($privacy);

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
    }

    public function updateEmail(
        string $email,
        User $user
    ): void
    {
        if (!is_null($this->userRepository->findOneBy(['email' => $email]))) {
            return;
        }

        $user->setEmail($email);

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
    }

    public function updatePassword(
        string $password,
        User $user
    ): void
    {
        if (password_verify($password, $user->getPassword())) {
            return;
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
    }

    public function updateTwoStepSecret(
        User $user
    ): string
    {
        $secret = $this->googleAuthenticator->generateSecret();

        $user->setGoogleAuthenticatorSecret($secret);

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);

        return $secret;
    }

    public function unsetTwoStepVerification(
        User $user
    ): void
    {
        $user
            ->setGoogleAuthenticatorSecret(null)
            ->setTwoFaVerified(null);
        ;

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
    }

    public function removeTwoFaOneTimeTokens(
        User $user
    ): void
    {
        $this->userTokenRepository->deleteTokensByUserAndTokenType($user, '2fa-one-time');
    }

    public function setTwoFaVerified(
        User $user
    ): void
    {
        $user->setTwoFaVerified(new DateTime());

        $this->userRepository->flushEntity();
        $this->deleteUserCache($user);
    }

    public function isTwoFaValid(
        User $user,
        string $code
    ): bool
    {
        return $this->googleAuthenticator->checkCode($user, $code);
    }

    private function deleteUserCache(
        User $user
    ): void
    {
        $this->cache->delete('user_' . $user->getNickname());
        $this->cache->delete('user_' . $user->getUuid());
    }


}
