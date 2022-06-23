<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Form\OAuth\LoginFormType;
use App\Repository\ClientRepository;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Security\SecurityService;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(
        private AuthenticationUtils $authenticationUtils,
        private ClientRepository $clientRepository,
        private SecurityService $securityService,
        private Security $security
    )
    {
    }

    /**
     * @Route("/login", name="login")
     */
    public function loginAction(): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('home');
        }

        $lastUsername = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('website/login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/logout", name="logout")
     */
    public function logoutAction()
    {
        //logout automatically
    }

    /**
     * @Route("/login/oauth/authorize", name="oauth_user_login", methods={"GET", "POST"})
     */
    public function oauthLoginAction(
        Request $request
    ): Response
    {
        $errors = [];
        $client = null;

        $query = $request->query->all() ?? [];

        if (array_key_exists('client_id', $query)) {
            $client = $this->clientRepository->findOneBy(['clientId' => $query['client_id']]);

            if (is_null($client)) {
                $errors[] = 'Client not found!';
                goto end;
            }
        }

        if (!array_key_exists('client_id', $query)) {
            $errors[] = 'client_id is missing in the query. Contact the client, from where you came from!';
            goto end;
        }

        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->get('approval')->getData()) {
            if (!array_key_exists('response_type', $query)) {
                $errors[] = 'response_type required!';
                goto end;
            }

            $state = null;

            if (array_key_exists('state', $query)) {
                $state = $query['state'];
            }

            if ($query['response_type'] !== 'code') {
                $errors[] = 'Wrong response_type';
                goto end;
            }

            $user = $this->securityService->getUserByCredentials($request);

            if (is_null($user)) {
                $errors[] = 'Email or password are false!';
                goto end;
            }

            //TODO: create authentication token and redirect user
        }

        end:

        return $this->renderForm('website/oauth/login.html.twig', [
            'login_form' => $form,
            'client_name' => is_null($client) ? '' : $client->getProject()->getProjectName(),
            'client_logo' => is_null($client) ?  : '/files/' . $client->getProject()->getOrganisationLogo(),
            'errors' => $errors
        ]);
    }


}
