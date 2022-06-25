<?php declare(strict_types=1);

namespace App\Service\API\Security;

use DateTime;
use App\Entity\User;
use App\Entity\Item;
use Firebase\JWT\JWT;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use App\Repository\AuthTokenRepository;
use App\Repository\AccessTokenRepository;
use App\Repository\RefreshTokenRepository;

final class SecurityService
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private AccessTokenRepository $accessTokenRepository,
        private AuthTokenRepository $authTokenRepository
    )
    {
    }

    public function generateAccessTokenForCCG(
        Client $client
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
            ->setScopes(['read', 'write', 'modify'])
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

    public function isClientAllowedForAdjustmentOnItem(
        string $token,
        Item $item
    ): bool
    {
        $accessTokenData = $this->accessTokenRepository->findOneBy(['accessToken' => $token]);

        return !is_null($accessTokenData) && $item->getUser()->getId() === $accessTokenData->getUser()->getId() && $item->getUser()->getId() === $accessTokenData->getUser()->getId();
    }

    public function createPayloadWithAccessAndRefreshToken(
        AuthToken $authToken
    ): array
    {
        $accessToken = new AccessToken();

        $expire = new DateTime('+10 day');

        $accessToken
            ->setUser($authToken->getUser())
            ->setProject($authToken->getProject())
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setExpireDate($expire)
            ->setScopes($authToken->getScopes())
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $refreshToken = new RefreshToken();

        $refreshToken
            ->setUser($authToken->getUser())
            ->setProject($authToken->getProject())
            ->setRefreshToken(bin2hex(random_bytes(64)))
            ->setExpireDate(new DateTime('+15 day'))
            ->setScopes($authToken->getScopes())
        ;

        $this->refreshTokenRepository->persistAndFlushEntity($refreshToken);

        $this->authTokenRepository->deleteEntry($authToken);

        $diff = $expire->diff(new DateTime());
        $expireInSeconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600) + ($diff->d * 3600 * 24);

        return [
            'access_token' => $this->generateJWT($accessToken->getAccessToken()),
            'token_type' => 'bearer',
            'expires_in' => $expireInSeconds,
            'refresh_token' => $this->generateJWT($refreshToken->getRefreshToken())
        ];
    }

    public function isClientAllowedForAdjustmentOnUserContent(
        string $token,
        User $user
    ): bool
    {
        $accessToken = $this->accessTokenRepository->findOneBy(['user' => $user, 'accessToken' => $token]);

        return !is_null($accessToken) && new DateTime() < $accessToken->getExpireDate();
    }


}