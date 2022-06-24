<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use App\Entity\User;
use App\Entity\WebApp;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Repository\UserRepository;
use App\Repository\AuthTokenRepository;
use Symfony\Component\HttpFoundation\Request;

final class SecurityService
{
    public function __construct(
        private AuthTokenRepository $authTokenRepository,
        private UserRepository $userRepository
    )
    {
    }

    public function getUserByCredentials(
        Request $request
    ): ?User
    {
        $data = $request->request->all('login_form');

        $user = $this->userRepository->findOneBy(['nickname' => $data['nickname']]);

        if (!is_null($user) && password_verify($data['password'], $user->getPassword())) {
            return $user;
        }

        return null;
    }

    public function hasClientAuthTokenFromUser(
        User $user,
        Client $client
    ): bool
    {
        $authToken = $this->authTokenRepository->findOneBy(['user' => $user, 'project' => $client->getProject()]);

        return !is_null($authToken);
    }

    public function createAuthenticationToken(
        User $user,
        Client $client,
        WebApp $webApp
    ): AuthToken
    {
        $authToken = new AuthToken();

        $authToken
            ->setUser($user)
            ->setProject($client->getProject())
            ->setAuthToken(bin2hex(random_bytes(64)))
            ->setScopes($webApp->getScopes())
        ;

        $this->authTokenRepository->persistAndFlushEntity($authToken);

        return $authToken;
    }


}