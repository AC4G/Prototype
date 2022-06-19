<?php declare(strict_types=1);

namespace App\Service\API\Security;

use DateTime;
use Firebase\JWT\JWT;
use App\Entity\Client;
use App\Entity\AccessToken;
use App\Repository\AccessTokenRepository;

final class SecurityService
{
    public function __construct(
        private AccessTokenRepository $accessTokenRepository
    )
    {
    }

    public function generateAccessTokenForCCG(
        Client $client,
        string $grantType
    ): array
    {
        $token = bin2hex(random_bytes(64));

        $accessToken = new AccessToken();

        $expire = new DateTime('+1 day');

        $accessToken
            ->setUser($client->getProject()->getDeveloper()->getUser())
            ->setAccessToken($token)
            ->setProject($client->getProject())
            ->setExpireDate($expire)
            ->setScopes(['read', 'write', 'modify', 'delete'])
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $diff = $expire->diff(new DateTime());
        $seconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600);

        return [
            'access_token' => $this->generateJWT($token),
            'token_type' => 'bearer',
            'expires_in' => $seconds
        ];
    }

    public function authorizeClientForGrantRestrictedAccess(): bool
    {


        return true;
    }

    protected function generateJWT(
        string $token
    ): string
    {
        $passphrase = '6913abde502dffb25b96af7c5a2e322304d48c89381d852edab6a4e98f343d618e1e8196858ea9193b869a4b50e43d2c65178260dd7a50d89d71cd9394bdcdef';
        $privateKeyFile = '../private.pem';

        $privateKey = openssl_get_privatekey(
            file_get_contents($privateKeyFile),
            $passphrase
        );

        return JWT::encode([
            'token' => $token
        ], $privateKey, 'RS256');
    }


}