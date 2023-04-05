<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\WebApp;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Entity\Organisation;
use App\Repository\ScopeRepository;
use App\Repository\ClientRepository;
use App\Repository\WebAppRepository;
use App\Repository\ProjectRepository;
use App\Repository\AuthTokenRepository;
use App\Repository\OrganisationRepository;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class SecurityService
{
    private ?Client $client = null;
    private ?Organisation $organisation = null;
    private ?WebApp $webApp = null;
    private array $scopes = [];

    public function __construct(
        private readonly OrganisationRepository $organisationRepository,
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly ClientRepository $clientRepository,
        private readonly WebAppRepository $webAppRepository,
        private readonly ScopeRepository $scopeRepository,
        private readonly CacheInterface $cache
    )
    {
    }

    public function validateQuery(
        array $query
    ): array
    {
        $errors = [];

        if (count($query) === 0) {
            $errors[] =  'Invalid request!';
        }

        if (!array_key_exists('response_type', $query)) {
            $errors[] = 'Request has not key "response_type"';
        }

        if (!array_key_exists('client_id', $query)){
            $errors[] = 'Request has not key "client_id"';
        }

        if (array_key_exists('response_type', $query) && $query['response_type'] !== 'code') {
            $errors[] = 'unsupported response_type';
        }

        return $errors;
    }

    public function prepareParameter(
        array $query,
        UserInterface $user
    ): array
    {
        $errors = [];

        $this->client = $this->clientRepository->getClientFromCacheById($query['client_id']);

        if (is_null($this->client)) {
            $errors[] = 'Client not found!';
        }

        if (!is_null($this->client)) {
            $this->organisation = $this->organisationRepository->getOrganisationFromCacheById($this->client->getProject()->getOrganisation()->getId());
        }

        if (!is_null($this->client) && $this->hasClientAuthTokenFromUserAndIsNotExpired($user, $this->client)) {
            $errors[] = 'Already authenticated!';
        }

        $this->webApp = $this->webAppRepository->getWebAppFromCacheByClient($this->client);

        if (!is_null($this->client) && (is_null($this->webApp) || (is_null($this->webApp->getRedirectUrl()) || strlen($this->webApp->getRedirectUrl()) === 0) || count($this->webApp->getScopes()) === 0)) {
            $errors[] = 'Unauthorized client!';
        }

        if (!is_null($this->webApp)) {
            $this->setScopes();

            if (!$this->areScopesQualified($query)) {
                $errors[] = 'Given scopes are not qualified!';
            }
        }

        return $errors;
    }

    private function areScopesQualified(
        array $query
    ): bool
    {
        if (!array_key_exists('scopes', $query)) {
            return true;
        }

        $scopes = explode(',', $query['scopes']);

        foreach ($scopes as $givenScope) {
            foreach ($this->scopes as $savedScope) {
                if ($givenScope !== $savedScope->getScope()) {
                    return false;
                }
            }
        }

        return true;
    }

    public function createAuthTokenAndBuildRedirectURI(
        array $query,
        UserInterface $user
    ): string
    {
        if (array_key_exists('scopes', $query)) {
            $scopes = explode(',', $query['scopes']);
        } else {
            $scopes = [];

            foreach ($this->scopes as $scope) {
                $scopes[] = $scope->getScope();
            }
        }

        $authToken = $this->createAuthenticationToken($user, $scopes);

        $redirectURI = $this->webApp->getRedirectUrl() . '?code=' . $authToken->getAuthToken();

        if (!array_key_exists('state', $query)) {
            return $redirectURI;
        }

        return $redirectURI . '&state=' . $query['state'];
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function getOrganisation(): ?Organisation
    {
        return $this->organisation;
    }

    public function getWebApp(): ?WebApp
    {
        return $this->webApp;
    }

    private function setScopes(): void
    {
        if (is_null($this->webApp)) {
            $this->scopes = [];
        }

        foreach ($this->webApp->getScopes() as $scopeId) {
            $this->scopes[] = $this->scopeRepository->getScopeById($scopeId);
        }
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    private function hasClientAuthTokenFromUserAndIsNotExpired(
        UserInterface $user,
        Client $client
    ): bool
    {
        $authToken = $this->authTokenRepository->findOneBy(['user' => $user, 'project' => $client->getProject()], ['id' => 'DESC']);

        return !is_null($authToken) && new DateTime() < $authToken->getExpireDate();
    }

    private function createAuthenticationToken(
        UserInterface $user,
        array $scopes
    ): AuthToken
    {
        $authToken = new AuthToken();

        $expire = new DateTime('+ 1days');

        $project = $this->projectRepository->findOneBy(['id' => $this->client->getProject()->getId()]);

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
