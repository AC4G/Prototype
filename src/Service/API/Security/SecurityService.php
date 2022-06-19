<?php declare(strict_types=1);

namespace App\Service\API\Security;

use App\Entity\Client;
use App\Repository\ClientRepository;

final class SecurityService
{
    public function __construct(
        private ClientRepository $clientRepository
    )
    {
    }

    public function findClient(
        string $clientId,
        string $clientSecret
    ): ?Client
    {
        return $this->clientRepository->findOneBy(['clientId' => $clientId, 'clientSecret' => $clientSecret]);
    }

    public function generateResponsePayloadWithJWT(
        Client $client
    ): string
    {


        return '';
    }

    public function authorizeClientForGrantRestrictedAccess(): bool
    {


        return true;
    }


}