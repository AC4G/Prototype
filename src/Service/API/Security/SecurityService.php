<?php declare(strict_types=1);

namespace App\Service\API\Security;

use DateTime;
use Exception;
use App\Entity\User;
use App\Entity\Item;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use App\Repository\UserRepository;
use App\Repository\AuthTokenRepository;
use App\Repository\UserRolesRepository;
use App\Repository\AccessTokenRepository;
use App\Repository\RefreshTokenRepository;

final class SecurityService
{
    public function __construct(
        private RefreshTokenRepository $refreshTokenRepository,
        private AccessTokenRepository $accessTokenRepository,
        private AuthTokenRepository $authTokenRepository,
        private UserRolesRepository $userRolesRepository,
        private UserRepository $userRepository
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

    protected function decodeJWTAndReturnToken(
        ?string $jwt
    ): ?string
    {
        $passphrase = '6913abde502dffb25b96af7c5a2e322304d48c89381d852edab6a4e98f343d618e1e8196858ea9193b869a4b50e43d2c65178260dd7a50d89d71cd9394bdcdef';
        $privateKeyFile = '../private.pem';

        $privateKey = openssl_get_privatekey(
            file_get_contents($privateKeyFile),
            $passphrase
        );

        $publicKey = openssl_pkey_get_details($privateKey)['key'];

        try {
            $payload = JWT::decode(
                $jwt,
                new Key($publicKey, 'RS256')
            );
        } catch (Exception $e) {
            return null;
        }

        return get_object_vars($payload)['token'];
    }

    public function createPayloadWithAccessAndRefreshTokenFromAuthToken(
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

    public function createPayloadWithAccessAndRefreshTokenFromRefreshToken(
        RefreshToken $oldRefreshToken,
        AccessToken $oldAccessToken
    ): array
    {
        $accessToken = new AccessToken();

        $expire = new DateTime('+10 day');

        $accessToken
            ->setUser($oldRefreshToken->getUser())
            ->setProject($oldRefreshToken->getProject())
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setExpireDate($expire)
            ->setScopes($oldRefreshToken->getScopes())
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $refreshToken = new RefreshToken();

        $refreshToken
            ->setUser($oldRefreshToken->getUser())
            ->setProject($oldRefreshToken->getProject())
            ->setRefreshToken(bin2hex(random_bytes(64)))
            ->setExpireDate(new DateTime('+15 day'))
            ->setScopes($oldRefreshToken->getScopes())
        ;

        $this->refreshTokenRepository->persistAndFlushEntity($refreshToken);

        $this->accessTokenRepository->deleteEntry($oldAccessToken);
        $this->refreshTokenRepository->deleteEntry($oldRefreshToken);

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
        ?string $jwt,
        User $user
    ): bool
    {
        if (is_null($jwt)) {
            return false;
        }

        $token = $this->decodeJWTAndReturnToken($jwt);

        if (is_null($token)) {
            return false;
        }

        $accessToken = $this->accessTokenRepository->findOneBy(['user' => $user, 'accessToken' => $token]);

        return !is_null($accessToken) && new DateTime() < $accessToken->getExpireDate();
    }

    public function isClientAllowedForAdjustmentOnItem(
        ?string $jwt,
        Item $item
    ): bool
    {
        if (is_null($jwt)) {
            return false;
        }

        $token = $this->decodeJWTAndReturnToken($jwt);

        if (is_null($token)) {
            return false;
        }

        $accessTokenData = $this->accessTokenRepository->findOneBy(['accessToken' => $token]);

        return !is_null($accessTokenData) && $item->getUser()->getId() === $accessTokenData->getUser()->getId() && $item->getUser()->getId() === $accessTokenData->getUser()->getId();
    }

    public function isClientAdmin(
        $jwt
    ): bool
    {
        if (is_null($jwt)) {
            return false;
        }

        $token = $this->decodeJWTAndReturnToken($jwt);

        if (is_null($token)) {
            return false;
        }

        $accessToken = $this->accessTokenRepository->findOneBy(['accessToken' => $token]);

        if (is_null($accessToken)) {
            return false;
        }

        $userRoles = $this->userRolesRepository->findBy(['user' => $accessToken->getProject()->getDeveloper()->getUser()]);

        foreach ($userRoles as $role) {
            if ($role->getRoleIdent()->getRoleName() === 'ROLE_ADMIN') {
                return true;
            }
        }

        return false;
    }

    public function nicknameExists(
        string $nickname
    ): bool
    {
        $user = $this->userRepository->findOneBy(['nickname' => $nickname]);

        return !is_null($user);
    }


}