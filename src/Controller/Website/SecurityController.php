<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Form\OAuth\OAuthFormType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Security\SecurityService;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class SecurityController extends AbstractController
{
    use TargetPathTrait;

    public function __construct(
        private readonly AuthenticationUtils $authenticationUtils,
        private readonly SecurityService $securityService,
        private readonly Security $security
    )
    {
    }

    #[Route('/login', name: 'login', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function loginAction(
        Request $request
    ): Response
    {
        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('home');
        }

        /** @var ?User $user **/
        $user = $this->getUser();

        if (!is_null($user) && $user->isTwoFaVerified()) {
            $request->getSession()->set('redirect', $this->getTargetPath($request->getSession(), 'main'));
        }

        $lastUsername = $this->authenticationUtils->getLastUsername();

        $error = $this->authenticationUtils->getLastAuthenticationError();

        return $this->render('react.base.html.twig', [
            'username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/loginSuccess', name: 'login_success', methods: [Request::METHOD_GET])]
    public function loginSuccess(
        Request $request
    ): Response
    {
        $redirect = $request->getSession()->get('redirect');

        if (!is_null($redirect)) {
            $request->getSession()->remove('redirect');

            return $this->redirect($redirect);
        }

        $previousPage = $this->getTargetPath($request->getSession(), 'main');

        if (!is_null($previousPage)) {
            return $this->redirect($previousPage);
        }

        return $this->redirectToRoute('home');
    }

    #[Route('/logout', name: 'logout', methods: [Request::METHOD_GET])]
    public function logoutAction()
    {
        //logout automatically
    }

    #[Route('/logoutSuccess', name: 'logout_success', methods: [Request::METHOD_GET])]
    public function logoutSuccess(
        Request $request
    ): RedirectResponse
    {
        $redirect = $request->getSession()->get('redirect');

        if (!is_null($redirect)) {
            return $this->redirectToRoute('login');
        }

        $request->getSession()->invalidate();

        return $this->redirectToRoute('home');
    }

    #[Route('/oauth2/authorize', name: 'oauth_authorize', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function oauthLoginAction(
        Request $request
    ): Response
    {
        $user = $this->getUser();

        if (is_null($user)) {
            $session = $request->getSession();
            $session->set('redirect', $request->getRequestUri());

            return $this->redirectToRoute('login');
        }

        $query = $request->query->all();
        $errors = $this->securityService->validateQuery($query);

        $request->getSession()->set('redirect', $request->getRequestUri());

        $form = $this->createForm(OAuthFormType::class);
        $form->handleRequest($request);

        if ((count($errors) === 0 && count($errors = $this->securityService->prepareParameter($query, $user)) > 0) || !$form->isSubmitted() || !$form->isValid() || count($errors) > 0) {
            return $this->renderForm('website/oauth/index.html.twig', [
                'user' => $user,
                'client' => $this->securityService->getClient(),
                'webApp' => $this->securityService->getWebApp(),
                'scopes' => $this->securityService->getScopes(),
                'form' => $form,
                'errors' => $errors
            ]);
        }

        $request->getSession()->remove('redirect');

        return $this->redirect($this->securityService->createAuthTokenAndBuildRedirectURI($query, $user));
    }

}
