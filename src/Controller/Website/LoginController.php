<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Form\Login\LoginFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    public function __construct(
        private AuthenticationUtils $authenticationUtils
    )
    {
    }

    /**
     * @Route("/login", name="login")
     */
    public function login(Request $request): Response
    {
        $user = new User();

        $form = $this->createForm(LoginFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

        }

        $lastUsername = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('website/login/index.html.twig', [
            'login_form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/login/success", name="login_success")
     */
    public function loginSuccess()
    {

    }
}
