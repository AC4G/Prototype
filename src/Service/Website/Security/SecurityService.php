<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\WebApp;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Repository\ProjectRepository;
use App\Repository\AuthTokenRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityService
{
    public function __construct(
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function isQueryValid(
        array $query
    ): bool
    {
        return array_key_exists('response_type', $query) && array_key_exists('client_id', $query) && $query['response_type'] === 'code';
    }

    public function hasClientAuthTokenFromUserAndIsNotExpired(
        UserInterface $user,
        Client $client
    ): bool
    {
        $authToken = $this->authTokenRepository->findOneBy(['user' => $user, 'project' => $client->getProject()], ['id' => 'DESC']);

        return !is_null($authToken) && new DateTime() < $authToken->getExpireDate();
    }

    public function areScopesQualified(
        array $query,
        array $webAppScopes
    ): bool
    {
        if (!array_key_exists('scopes', $query)) {
            return true;
        }

        $scopes = explode(',', $query['scopes']);

        foreach ($scopes as $givenScope) {
            foreach ($webAppScopes as $savedScope) {
                if ($givenScope !== $savedScope->getScope()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function createAuthTokenAndBuildRedirectURI(
        array $query,
        UserInterface $user,
        Client $client,
        WebApp $webApp,
        array $savedScopes
    ): string
    {
        if (array_key_exists('scopes', $query)) {
            $scopes = explode(',', $query['scopes']);
        } else {
            $scopes = [];

            foreach ($savedScopes as $scope) {
                $scopes[] = $scope->getScope();
            }
        }

        $authToken = $this->createAuthenticationToken($client, $user, $scopes);

        $redirectURI = $webApp->getRedirectUrl() . '?code=' . $authToken->getAuthToken();

        if (!array_key_exists('state', $query)) {
            return $redirectURI;
        }

        return $redirectURI . '&state=' . $query['state'];
    }

    private function createAuthenticationToken(
        Client $client,
        UserInterface $user,
        array $scopes
    ): AuthToken
    {
        $authToken = new AuthToken();

        $expire = new DateTime('+ 1days');

        $project = $this->projectRepository->findOneBy(['id' => $client->getProject()->getId()]);

        $authToken
            ->setUser($user)
            ->setProject($project)
            ->setAuthToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes($scopes)
        ;

        $this->authTokenRepository->persistAndFlushEntity($authToken);

        $diff = $expire->diff(new DateTime());
        $seconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600) + ($diff->d * 3600 * 24);

        $authTokenCache = $this->cache->getItem('authToken_' . $authToken->getAuthToken());

        $authTokenCache
            ->set($authToken)
            ->expiresAfter($seconds)
        ;

        $this->cache->save($authTokenCache);

        return $authToken;
    }


}
