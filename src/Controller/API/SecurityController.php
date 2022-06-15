<?php declare(strict_types=1);

namespace App\Controller\API;

use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Server\AuthorizationServer;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SecurityController extends AbstractController
{
    public function __construct(

    )
    {
    }

    /**
     * @Route("/api/accessToken", name="client_access_token", methods={"POST"})
     */
    public function getClientAccessToken(
        Request $request
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

    }


}