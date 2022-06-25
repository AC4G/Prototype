<?php declare(strict_types=1);

namespace App\Controller\API;

use DateTime;
use App\Repository\ClientRepository;
use App\Repository\AuthTokenRepository;
use App\Repository\AccessTokenRepository;
use App\Repository\RefreshTokenRepository;
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
        private RefreshTokenRepository $refreshTokenRepository,
        private AccessTokenRepository $accessTokenRepository,
        private AuthTokenRepository $authTokenRepository,
        private ClientRepository $clientRepository,
        private SecurityService $securityService,
        private CustomResponse $customResponse
    )
    {
    }

    /**
     * @Route("/api/authorize", name="client_authorization", methods={"POST"})
     */
    public function authorizeClient(
        Request $request
    ): Response
    {
        $content = $request->request->all();

        /*
         * grant_type
         *  - Client Credentials Grant -> client_credentials
         *  - Authorization Code Grant -> authorization_code
         *  - Refresh Token Grant -> refresh_token
         */
        if (!array_key_exists('grant_type', $content)) {
            return $this->customResponse->errorResponse($request, 'grant_type required!', 406);
        }

        if (!array_key_exists('client_id', $content) xor !array_key_exists('client_secret', $content)) {
            return $this->customResponse->errorResponse($request, 'Client credentials required!', 406);
        }

        $client = $this->clientRepository->findOneBy(['clientId' => $content['client_id'], 'clientSecret' => $content['client_secret']]);

        if (is_null($client)) {
            return $this->customResponse->errorResponse($request, 'Rejected!', 403);
        }

        if ($content['grant_type'] === 'authorization_code') {
            /*  Start of 'Authorization Code Grant'
            *  - access token for client resources
            */

            if (!array_key_exists('code', $content)) {
                return $this->customResponse->errorResponse($request, 'code required!', 406);
            }

            $authToken = $this->authTokenRepository->findOneBy(['authToken' => $content['code'], 'project' => $client->getProject()]);

            if (is_null($authToken)) {
                return $this->customResponse->errorResponse($request, 'Rejected!', 403);
            }

            $payload = $this->securityService->createPayloadWithAccessAndRefreshTokenFromAuthToken($authToken);

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
            /*  Start Refresh Token Grant
             *  - access token for client resources
             */
            if (!array_key_exists('refresh_token', $content)) {
                return $this->customResponse->errorResponse($request, 'refresh_token required!', 406);
            }

            $refreshToken = $this->refreshTokenRepository->findOneBy(['refreshToken' => $content['refresh_token'], 'project' => $client->getProject()]);

            if (is_null($refreshToken)) {
                return $this->customResponse->errorResponse($request, 'Rejected!', 403);
            }

            $accessToken = $this->accessTokenRepository->findOneBy(['project' => $client->getProject(), 'user' => $refreshToken->getUser()]);

            if (is_null($accessToken) || new DateTime() > $accessToken->getExpireDate()) {
                return $this->customResponse->errorResponse($request, 'Rejected!', 403);
            }

            $payload = $this->securityService->createPayloadWithAccessAndRefreshTokenFromRefreshToken($refreshToken, $accessToken);

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

        /*  Start of 'Client Credentials Grant'
         *  - access token only for client resources
         */

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


}
