<?php declare(strict_types=1);

namespace App\Service\Website\Security;

use DateTime;
use App\Entity\WebApp;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Repository\ClientRepository;
use App\Repository\WebAppRepository;
use App\Repository\ScopeRepository;
use App\Repository\AuthTokenRepository;
use Symfony\Component\Security\Core\User\UserInterface;


final class SecurityService
{

    private ?Client $client = null;
    private ?WebApp $webApp = null;
    private array $scopes = [];

    public function __construct(
        private AuthTokenRepository $authTokenRepository,
        private ClientRepository $clientRepository,
        private WebAppRepository $webAppRepository,
        private ScopeRepository $scopeRepository
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

        $this->client = $this->clientRepository->findOneBy(['clientId' => $query['client_id']]);

        if (is_null($this->client)) {
            $errors[] = 'Client not found!';
        }

        if (!is_null($this->client) && $this->hasClientAuthTokenFromUser($user, $this->client)) {
            $errors[] = 'Already authenticated!';
        }

        $this->webApp = $this->webAppRepository->findOneBy(['client' => $this->client]);

        if (!is_null($this->client) && (is_null($this->webApp) || (is_null($this->webApp->getRedirectUrl()) || strlen($this->webApp->getRedirectUrl()) === 0) || count($this->webApp->getScopes()) === 0)) {
            $errors[] = 'Unauthorized client!';
        }

        return $errors;
    }

    public function createAuthTokenAndBuildRedirectUri(
        array $query,
        UserInterface $user
    ): string
    {
        $authToken = $this->createAuthenticationToken($user, $this->client, $this->webApp);

        $state = '';

        if (array_key_exists('state', $query)) {
            $state = $query['state'];
        }

        return $this->webApp->getRedirectUrl() . '?code=' . $authToken->getAuthToken() . '&state=' . $state;
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
        $authToken = $this->authTokenRepository->findOneBy(['user' => $user, 'project' => $client->getProject()]);

        return !is_null($authToken);
    }

    private function createAuthenticationToken(
        UserInterface $user,
        Client $client,
        WebApp $webApp
    ): AuthToken
    {
        $authToken = new AuthToken();

        $authToken
            ->setUser($user)
            ->setProject($client->getProject())
            ->setAuthToken(bin2hex(random_bytes(64)))
            ->setExpireDate(new DateTime('+ 7days'))
            ->setScopes($webApp->getScopes())
        ;

        $this->authTokenRepository->persistAndFlushEntity($authToken);

        return $authToken;
    }


}
