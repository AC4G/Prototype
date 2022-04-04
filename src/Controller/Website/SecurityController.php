<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        private AuthenticationUtils $authenticationUtils
    )
    {
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(): Response
    {
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('website/login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/login/success", name="login_success")
     */
    public function loginSuccess(): Response
    {
        return $this->render('website/login/success.html.twig');
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        //logout automatically
    }
}
