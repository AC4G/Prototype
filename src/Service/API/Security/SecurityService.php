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
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class SecurityService
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly UserRolesRepository $userRolesRepository,
        private readonly UserRepository $userRepository,
        private readonly ContainerBagInterface $params
    )
    {
    }

    public function generateAccessTokenForCCG(
        Client $client
    ): array
    {
        $accessToken = new AccessToken();

        $expire = new DateTime('+1 day');

        $accessToken
            ->setUser($client->getProject()->getDeveloper()->getUser())
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setProject($client->getProject())
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes(['read', 'write', 'modify'])
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $diff = $expire->diff(new DateTime());
        $seconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600);

        return [
            'access_token' => $this->generateJWT($accessToken->getAccessToken()),
            'token_type' => 'bearer',
            'expires_in' => $seconds
        ];
    }

    protected function generateJWT(
        string $token
    ): string
    {
        $passphrase = $this->params->get('app.passphrase');
        $privateKeyFile = '../rsaKeys/private.pem';

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
        $passphrase = $this->params->get('app.passphrase');
        $privateKeyFile = '../rsaKeys/private.pem';

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

    public function buildPayloadWithAccessAndRefreshToken(
        AuthToken|RefreshToken $token,
        string $grantType,
        AccessToken $oldAccessToken = null
    ): array
    {
        $accessToken = new AccessToken();

        $expire = new DateTime('+10 day');

        $accessToken
            ->setUser($token->getUser())
            ->setProject($token->getProject())
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes($token->getScopes())
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $refreshToken = new RefreshToken();

        $refreshToken
            ->setUser($token->getUser())
            ->setProject($token->getProject())
            ->setRefreshToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate(new DateTime('+15 day'))
            ->setScopes($token->getScopes())
        ;

        $this->refreshTokenRepository->persistAndFlushEntity($refreshToken);

        $this->authTokenRepository->deleteEntry($token);

        if ($grantType === 'refresh_token' && $oldAccessToken instanceof AccessToken) {
            $this->accessTokenRepository->deleteEntry($oldAccessToken);
        }

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
        string $jwt,
        Item $item
    ): bool
    {
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
        return !is_null($this->userRepository->findOneBy(['nickname' => $nickname]));
    }

    public function emailExists(
        string $email
    ): bool
    {
        return !is_null($this->userRepository->findOneBy(['email' => $email]));
    }


}
