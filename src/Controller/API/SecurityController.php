<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\Response\API\CustomResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\API\Security\SecurityService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    public function __construct(
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
    )
    {
        $content = $request->request->all();

        /*
         * grant_type
         *  - Client Credentials Grant -> client_credentials
         *  - Authorization Code Grant -> authorization_code
         */
        if (!array_key_exists('grant_type', $content)) {
            return $this->customResponse->errorResponse($request, 'grant_type required!', 406);
        }

        if (!array_key_exists('client_id', $content) && !array_key_exists('client_secret', $content)) {
            return $this->customResponse->errorResponse($request, 'client_id required!', 406);
        }

        if ($content['grant_type'] === 'authorization_code') {
            if (!array_key_exists('code', $content)) {
                return $this->customResponse->errorResponse($request, 'code required!', 406);
            }

            //If client id and secret matches to the auth. token, return access and refresh token
        }

        /*Start of 'Client Credentials Grant'
         *  - access token only for client resources
         */

        if ($content['grant_type'] !== 'client_credentials') {
            return $this->customResponse->errorResponse($request, 'Invalid grant_type!', 406);
        }

        $client = $this->securityService->findClient($content['client_id'], $content['client_secret']);

        if (is_null($client)) {
            return $this->customResponse->errorResponse($request, 'Rejected!', 400);
        }

        $payload = $this->securityService->generateResponsePayloadWithJWT($client);

        $response = new JsonResponse(
            $payload,
            200,
            [
                'Content-Type' => 'application/json',
                'Cache-Control' => 'no-store',
                'Pragma' => 'no-cache'
            ]
        );

        $response->setCharset('UTF-8');

        return $response;
    }


}
