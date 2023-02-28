<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Entity\User;
use App\Serializer\UserNormalizer;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Account\AccountService;
use App\Service\Website\Dashboard\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{

    public function __construct(
        private readonly UserNormalizer $userNormalizer,
        private readonly ProfileService $profileService,
        private readonly AccountService $accountService,
        private readonly Security $security
    )
    {
    }

    #[Route('/dashboard/profile', name: 'dashboard_profile', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function showProfile(
        Request $request
    ): Response
    {
        /** @var User $user **/
        $user = $this->security->getUser();

        if ($request->isMethod('POST') && $this->isCsrfTokenValid($request->request->get('form'), $request->request->get('token'))) {
            $this->profileService->updateProfile($user, $request);
        }

        $user = $this->userNormalizer->normalize($user);

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }

    #[Route('/dashboard/profile/2-step-verification', name: 'dashboard_profile_two_factor_authentication', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function twoStepVerificationAction(
        Request $request
    ): Response
    {
        $query = $request->query->all();

        if (!array_key_exists('action', $query)) {
            $this->addFlash('error', 'The URL was broken');

            return $this->redirectToRoute('dashboard_profile');
        }

        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isTwoFaVerified() && !$user->isGoogleAuthenticatorEnabled() && $query['action'] === 'disable') {
            $this->addFlash('error', '2-Step Verification already disabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($query['action'] === 'cancel' && !$user->isTwoFaVerified()) {
            $this->profileService->removeTokensAndUnsetTwofa($user);

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($query['action'] === 'enable' && $user->isTwoFaVerified()) {
            $this->addFlash('error', '2-Step Verification already enabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($query['action'] === 'disable' && !$user->isTwoFaVerified()) {
            $this->addFlash('error', '2-Step Verification already disabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        $code = $request->request->get('code');

        if ($request->isMethod('POST') && $query['action'] === 'disable' && $this->isCsrfTokenValid('disable_2fa', $request->request->get('token'))) {
            if (!$this->profileService->isTwofaCodeValid($user, $code)) {
                $this->addFlash('error', 'The code is wrong!');

                return $this->render('website/security/2fa_desetup.html.twig', [
                    'action' => $this->redirectToRoute('dashboard_profile_two_factor_authentication')->getTargetUrl() . '?action=disable'
                ]);
            }

            $this->profileService->removeTokensAndUnsetTwofa($user);
            $this->addFlash('success', '2-Step Verification successfully disabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($request->isMethod('POST') && $query['action'] === 'enable' && $this->isCsrfTokenValid('enable_2fa', $request->request->get('token'))) {
            if (!$this->profileService->isTwofaCodeValid($user, $code)) {
                $this->addFlash('error', 'The code is wrong!');

                return $this->render('website/security/2fa_setup.html.twig', [
                    'code' => $user->getGoogleAuthenticatorSecret(),
                    'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user)
                ]);
            }

            $this->profileService->verifyTwoStepSecret($user);
            $this->addFlash('success', '2-Step Verification successfully enabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($query['action'] === 'disable') {
           return $this->render('website/security/2fa_desetup.html.twig');
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            $this->accountService->updateTwoStepSecret($user);
        }

        return $this->render('website/security/2fa_setup.html.twig', [
            'code' => $user->getGoogleAuthenticatorSecret(),
            'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user),
            'reset_tokens' => $this->profileService->getOrGenerateTwoStepVerificationOneTimeTokens($user)
        ]);
    }


}
