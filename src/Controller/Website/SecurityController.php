<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Entity\User;
use App\Repository\ScopeRepository;
use App\Repository\ClientRepository;
use App\Repository\WebAppRepository;
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
        private readonly ClientRepository $clientRepository,
        private readonly WebAppRepository $webAppRepository,
        private readonly ScopeRepository $scopeRepository,
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

        return $this->render('website/login/index.html.twig', [
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

        if (!$this->securityService->isQueryValid($query)) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'OAuth url is not valid!'
            ]);
        }

        $client = $this->clientRepository->getClientFromCacheById($query['client_id']);

        if (is_null($client)) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'The client not found!'
            ]);
        }

        $webApp = $this->webAppRepository->getWebAppFromCacheByClient($client);

        if (is_null($webApp)) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'Web app not found!'
            ]);
        }

        if ($this->securityService->hasClientAuthTokenFromUserAndIsNotExpired($user, $client)) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'Auth token already created!'
            ]);
        }

        $scopes = $this->scopeRepository->getScopesByIds($webApp->getScopes());

        if (count($scopes) === 0) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'The client has wrong settings!'
            ]);
        }

        if (!$this->securityService->areScopesQualified($query, $scopes)) {
            return $this->render('website/oauth/error.html.twig', [
                'error' => 'Given scopes are not qualified!'
            ]);
        }

        if (!$request->isMethod('POST')) {
            return $this->renderForm('website/oauth/index.html.twig', [
                'user' => $user,
                'client' => $client,
                'webApp' => $webApp,
                'scopes' => $scopes
            ]);
        }

        return $this->redirect($this->securityService->createAuthTokenAndBuildRedirectURI($query, $user, $client, $webApp, $scopes));
    }

}
