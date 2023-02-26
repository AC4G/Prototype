<?php declare(strict_types=1);

namespace App\Controller\API;

use DateTime;
use App\Repository\ClientRepository;
use App\Repository\AuthTokenRepository;
use App\Repository\AccessTokenRepository;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SecurityController extends AbstractController
{
    public function __construct(
        private readonly AccessTokenRepository $accessTokenRepository,
        private readonly AuthTokenRepository $authTokenRepository,
        private readonly ClientRepository $clientRepository,
        private readonly SecurityService $securityService,
        private readonly CustomResponse $customResponse,
        private readonly CacheInterface $cache
    )
    {
    }

    #[Route('/api/authorize', name: 'client_authorization', methods: [Request::METHOD_POST])]
    public function authorizeClient(
        Request $request
    ): Response
    {
        $content = $request->request->all();

        if (!array_key_exists('grant_type', $content)) {
            return $this->customResponse->errorResponse($request, 'grant_type required!', 406);
        }

        if (!array_key_exists('client_id', $content) || !array_key_exists('client_secret', $content)) {
            return $this->customResponse->errorResponse($request, 'Client credentials required!', 406);
        }

        $client = $this->cache->get('client_' . $content['client_id'], function (ItemInterface $item) use ($content) {
            $item->expiresAfter(86400);

            return $this->clientRepository->findOneBy(['clientId' => $content['client_id']]);
        });

        if (is_null($client) || $client->getClientSecret() !== $content['client_secret']) {
            return $this->customResponse->errorResponse($request, 'Permission denied!', 403);
        }

        if ($content['grant_type'] === 'authorization_code') {
            if (!array_key_exists('code', $content)) {
                return $this->customResponse->errorResponse($request, 'code required!', 406);
            }

            $authToken = $this->cache->get('authToken_' . $content['code'], function (ItemInterface $item) use ($content) {
                $item->expiresAfter(86400);

                $this->authTokenRepository->findOneBy(['authToken' => $content['code']]);
            });

            if (is_null($authToken) || $authToken->getProject()->getId() !== $client->getProject()->getId()) {
                return $this->customResponse->errorResponse($request, 'Permission denied!', 403);
            }

            if (new DateTime() > $authToken->getExpireDate()) {
                return $this->customResponse->errorResponse($request, 'Auth token is expired!', 403);
            }

            $payload = $this->securityService->buildPayloadWithAccessAndRefreshToken($authToken, 'authorization_code');

            return new JsonResponse(
                $payload,
                200,
                [
                    'Content-Type' => 'application/json;charset=UTF-8',
                    'Cache-Control' => 'no-store',
                    'Pragma' => 'no-cache'
                ]
            );
        }

        if ($content['grant_type'] === 'refresh_token') {
            if (!array_key_exists('refresh_token', $content)) {
                return $this->customResponse->errorResponse($request, 'refresh_token required!', 406);
            }

            $payload = $this->securityService->decodeJWTAndReturnPayload($content['refresh_token']);

            if (is_null($payload)) {
                return $this->customResponse->errorResponse($request, 'Permission denied!', 403);
            }

            $refreshToken = json_decode($payload, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->customResponse->errorResponse($request, 'Corrupted refresh token, retry again or create a new one!', 500);
            }

            $accessToken = $this->accessTokenRepository->findOneBy(['project' => $client->getProject(), 'user' => is_array($refreshToken['user']) ? $refreshToken['user']['id'] : null]);

            if (is_null($accessToken)) {
                return $this->customResponse->errorResponse($request, 'No relatable access token found!', 403);
            }

            if (new DateTime() < $accessToken->getExpireDate()) {
                return $this->customResponse->errorResponse($request, 'Access token is not yet expired!', 403);
            }

            $payload = $this->securityService->buildPayloadWithAccessAndRefreshToken($refreshToken, 'refresh_token',$accessToken);

            return new JsonResponse(
                $payload,
                200,
                [
                    'Content-Type' => 'application/json;charset=UTF-8',
                    'Cache-Control' => 'no-store',
                    'Pragma' => 'no-cache'
                ]
            );
        }

        if ($content['grant_type'] !== 'client_credentials') {
            return $this->customResponse->errorResponse($request, 'Invalid grant_type!', 406);
        }

        $payload = $this->securityService->generateAccessTokenForCCG($client);

        return new JsonResponse(
            $payload,
            200,
            [
                'Content-Type' => 'application/json;charset=UTF-8',
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache'
            ]
        );
    }

    #[Route('/api/nickname/{nickname}', name: 'nickname_exists', methods: [Request::METHOD_GET])]
    public function nicknameExists(
        Request $request,
        string $nickname
    ): Response {
        return new JsonResponse([
            'pointer' => $request->getUri(),
            'nickname' => $nickname,
            'massage' => (int)$this->securityService->nicknameExists(urldecode($nickname))
        ]);
    }

    #[Route('/api/email/{email}', name: 'email_exists', methods: [Request::METHOD_GET])]
    public function emailExists(
        Request $request,
        string $email
    ): Response {
        return new JsonResponse([
            'pointer' => $request->getUri(),
            'email' => $email,
            'massage' => (int)$this->securityService->emailExists(urldecode($email))
        ]);
    }


}
