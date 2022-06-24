<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Form\OAuth\LoginFormType;
use App\Repository\ClientRepository;
use App\Repository\WebAppRepository;
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
        private WebAppRepository $webAppRepository,
        private SecurityService $securityService,
        private Security $security
    )
    {
    }

    /**
     * @Route("/login", name="login", methods={"GET", "POST"})
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
        $error = '';
        $client = null;

        $query = $request->query->all() ?? [];

        if (array_key_exists('client_id', $query)) {
            $client = $this->clientRepository->findOneBy(['clientId' => $query['client_id']]);

            if (is_null($client)) {
                $error = 'Client not found!';
                goto end;
            }
        }

        if (!array_key_exists('client_id', $query)) {
            $error = 'invalid_request';
            goto end;
        }

        $user = new User();
        $form = $this->createForm(LoginFormType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->get('approval')->getData()) {
            if (!array_key_exists('response_type', $query)) {
                $error = 'response_type required!';
                goto end;
            }

            $state = null;

            if (array_key_exists('state', $query)) {
                $state = $query['state'];
            }

            if ($query['response_type'] !== 'code') {
                $error = 'unsupported_response_type';
                goto end;
            }

            $user = $this->securityService->getUserByCredentials($request);

            if (is_null($user)) {
                $error = 'Email or password are false!';
                goto end;
            }

            if ($this->securityService->hasClientAuthTokenFromUser($user, $client)) {
                $error = 'Already authenticated!';
                goto end;
            }

            $webApp = $this->webAppRepository->findOneBy(['client' => $client]);

            if (is_null($webApp) || is_null($webApp->getRedirectUrl()) || count($webApp->getScopes()) === 0) {
                $error = 'unauthorized_client';
                goto end;
            }

            $authToken = $this->securityService->createAuthenticationToken($user, $client, $webApp);

            $redirectUri = $webApp->getRedirectUrl() . '?code=' . $authToken->getAuthToken();

            if (!is_null($state)) {
                $redirectUri = $redirectUri . '&state=' . $state;
            }

            return $this->redirect($redirectUri);
        }

        end:

        return $this->renderForm('website/oauth/login.html.twig', [
            'login_form' => $form,
            'client_name' => is_null($client) ? '' : $client->getProject()->getProjectName(),
            'client_logo' => is_null($client) ?  : '/files/' . $client->getProject()->getOrganisationLogo(),
            'error' => $error
        ]);
    }


}
