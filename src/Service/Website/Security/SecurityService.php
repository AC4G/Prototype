<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\WebApp;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Repository\ScopeRepository;
use App\Repository\ClientRepository;
use App\Repository\WebAppRepository;
use App\Repository\ProjectRepository;
use App\Repository\AuthTokenRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SecurityService
{

    private ?Client $client = null;
    private ?WebApp $webApp = null;
    private array $scopes = [];

    public function __construct(
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly ClientRepository $clientRepository,
        private readonly WebAppRepository $webAppRepository,
        private readonly ScopeRepository $scopeRepository,
        private readonly CacheInterface $cache,

        private readonly ProjectRepository $projectRepository,
        private readonly NormalizerInterface $normalizer
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

        $this->client = $this->cache->get('client_'. $query['client_id'], function (ItemInterface $item) use ($query) {
            $item->expiresAfter(86400);

            return $this->clientRepository->findOneBy(['clientId' => $query['client_id']]);
        });

        if (is_null($this->client)) {
            $errors[] = 'Client not found!';
        }

        if (!is_null($this->client) && $this->hasClientAuthTokenFromUser($user, $this->client)) {
            $errors[] = 'Already authenticated!';
        }

        $this->webApp = $this->cache->get('webApp_'. $query['client_id'], function (ItemInterface $item) use ($query) {
            $item->expiresAfter(86400);

            return $this->webAppRepository->findOneBy(['client' => $this->client]);
        });

        if (!is_null($this->client) && (is_null($this->webApp) || (is_null($this->webApp->getRedirectUrl()) || strlen($this->webApp->getRedirectUrl()) === 0) || count($this->webApp->getScopes()) === 0)) {
            $errors[] = 'Unauthorized client!';
        }

        return $errors;
    }

    public function createAuthTokenAndBuildRedirectURI(
        array $query,
        UserInterface $user
    ): string
    {
        $authToken = $this->createAuthenticationToken($user, $this->client, $this->webApp);

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

    public function getWebApp(): ?WebApp
    {
        return $this->webApp;
    }

    public function getScopes(): array
    {
        if (is_null($this->webApp)) {
            return [];
        }

        foreach ($this->webApp->getScopes() as $scopeId) {
            $this->scopes[] = $this->scopeRepository->findOneBy(['id' => $scopeId]);
        }

        return $this->scopes;
    }

    private function hasClientAuthTokenFromUser(
        UserInterface $user,
        Client $client
    ): bool
    {
        return !is_null($this->authTokenRepository->findOneBy(['user' => $user, 'project' => $client->getProject()]));
    }

    private function createAuthenticationToken(
        UserInterface $user,
        Client $client,
        WebApp $webApp
    ): AuthToken
    {
        $authToken = new AuthToken();

        $expire = new DateTime('+ 7days');

        $project = $this->cache->get('project_' . $client->getProject()->getId(), function (ItemInterface $item) use ($client) {
            $item->expiresAfter(86400);

            return $this->projectRepository->findOneBy(['id' => $client->getProject()->getId()]);
        });

        $authToken
            ->setUser($user)
            ->setProject($project)
            ->setAuthToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes($webApp->getScopes())
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
