<?php declare(strict_types=1);

namespace App\Service\API\Security;

use DateTime;
use Exception;
use App\Entity\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Entity\Client;
use App\Entity\AuthToken;
use App\Entity\AccessToken;
use App\Entity\RefreshToken;
use App\Repository\UserRepository;
use App\Repository\ClientRepository;
use App\Repository\ProjectRepository;
use App\Serializer\AccessTokenNormalizer;
use App\Repository\AccessTokenRepository;
use App\Serializer\RefreshTokenNormalizer;
use App\Repository\RefreshTokenRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class SecurityService
{
    public function __construct(
        private readonly RefreshTokenRepository $refreshTokenRepository,
        private readonly RefreshTokenNormalizer $refreshTokenNormalizer,
        private readonly AccessTokenNormalizer $accessTokenNormalizer,
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly ProjectRepository $projectRepository,
        private readonly ClientRepository $clientRepository,
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

        $client = $this->clientRepository->findOneBy(['id' => $client->getId()]);

        $accessToken
            ->setUser(null)
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setProject($client->getProject())
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes([])
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $diff = $expire->diff(new DateTime());
        $seconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600);

        return [
            'access_token' => $this->encodePayloadToJWT(json_encode($this->accessTokenNormalizer->normalize($accessToken))),
            'token_type' => 'bearer',
            'expires_in' => $seconds
        ];
    }

    private function encodePayloadToJWT(
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

    public function decodeJWTAndReturnPayload(
        string $jwt
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
        AuthToken|array $token,
        string $grantType,
        AccessToken $oldAccessToken = null
    ): array
    {
        $accessToken = new AccessToken();

        $expire = new DateTime('+10 day');

        /** @var User $user */
        $user = $token->getUser();
        $user = $this->userRepository->findOneBy(['id' => is_array($token) ? $token['user']['id'] : $user->getId()]);
        $project = $this->projectRepository->findOneBy(['id' => is_array($token) ? $token['project']['id'] : $token->getProject()->getId()]);

        $accessToken
            ->setUser($user)
            ->setProject($project)
            ->setAccessToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate($expire)
            ->setScopes(is_array($token) ? $token['scopes'] : $token->getScopes())
        ;

        $this->accessTokenRepository->persistAndFlushEntity($accessToken);

        $refreshToken = new RefreshToken();

        $refreshToken
            ->setUser($user)
            ->setProject($project)
            ->setRefreshToken(bin2hex(random_bytes(64)))
            ->setCreationDate(new DateTime())
            ->setExpireDate(new DateTime('+15 day'))
            ->setScopes(is_array($token) ? $token['scopes'] : $token->getScopes())
        ;

        $this->refreshTokenRepository->persistAndFlushEntity($refreshToken);

        if ($grantType === 'refresh_token' && $oldAccessToken instanceof AccessToken) {
            $this->accessTokenRepository->deleteEntry($oldAccessToken);
        }

        $diff = $expire->diff(new DateTime());
        $seconds = $diff->s + ($diff->m * 60) + ($diff->h * 3600) + ($diff->d * 3600 * 24);

        return [
            'access_token' => $this->encodePayloadToJWT(json_encode($this->accessTokenNormalizer->normalize($accessToken))),
            'token_type' => 'bearer',
            'expires_in' => $seconds,
            'refresh_token' => $this->encodePayloadToJWT(json_encode($this->refreshTokenNormalizer->normalize($refreshToken)))
        ];
    }

    public function hasClientPermissionForUserRelatedDataAction(
        array $accessToken,
        User $user,
        Request $request
    ): bool
    {
        $scopes = $request->attributes->get('scopes');

        if (!in_array('user.information.read', $scopes) && $request->isMethod('GET') && $request->attributes->get('_route') === 'api_user_by_uuid') {
            return false;
        }

        return $accessToken['user']['id'] === $user->getId();
    }

    public function hasClientPermissionForInventoryAction(
        array $accessToken,
        User $user,
        Request $request,
        ?array $item
    ): bool
    {
        $scopes = $request->attributes->get('scopes');

        if (is_null($item)) {
            return $accessToken['user']['id'] === $user->getId() && (in_array('inventory.read', $scopes) && $request->isMethod('GET'));
        }

        if ((!in_array('inventory.read', $scopes) && $request->isMethod('GET'))
            || (!in_array('inventory.write', $scopes) && $request->isMethod('POST'))
            || (!in_array('inventory.update', $scopes) && $request->isMethod('PATCH'))
            || (!in_array('inventory.delete', $scopes) && $request->isMethod('DELETE'))) {
            return false;
        }

        return $accessToken['user']['id'] === $user->getId() && $item['project']['id'] === $accessToken['project']['id'];
    }

    public function hasClientPermissionForItemAction(
        array $accessToken,
        array $item
    ): bool
    {
        if (is_null($item['project'])) {
            return false;
        }

        return $item['project']['id'] === $accessToken['project']['id'];
    }

    public function nicknameExists(
        string $nickname
    ): bool
    {
        return !is_null($this->userRepository->getUserByUuidOrNicknameFromCache($nickname));
    }

    public function emailExists(
        string $email
    ): bool
    {
        return !is_null($this->userRepository->findOneBy(['email' => $email]));
    }


}
