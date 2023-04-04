<?php declare(strict_types=1);

namespace App\Controller\Website\Settings;

use App\Entity\User;
use App\Serializer\UserNormalizer;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Account\AccountService;
use App\Service\Website\Settings\ProfileService;
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

    #[Route('/settings/profile', name: 'settings_profile', methods: [Request::METHOD_GET, Request::METHOD_POST])]
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

        return $this->render('website/settings/profile/index.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }

    #[Route('/settings/profile/2fa/disable', name: 'two_factor_authentication_disable')]
    public function twoStepVerificationDisable(
        Request $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user->isTwoFaVerified() && !$user->isGoogleAuthenticatorEnabled()) {
            $this->addFlash('error', '2-Step Verification already disabled!');

            return $this->redirectToRoute('settings_profile');
        }

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('disable_2fa', $request->request->get('token'))) {
            if (!$this->profileService->isTwoFaCodeValid($user, $request->request->get('code'))) {
                return $this->render('website/security/2fa_disable.html.twig', [
                    'error' => 'The verification code is not valid.'
                ]);
            }

            $this->profileService->removeTokensAndUnsetTwoFa($user);
            $this->addFlash('success', '2-Step Verification successfully disabled!');

            return $this->redirectToRoute('settings_profile');
        }

        return $this->render('website/security/2fa_disable.html.twig', [
            'error' => null
        ]);
    }

    #[Route('/settings/profile/2fa/enable', name: 'two_factor_authentication_enable')]
    public function twoStepVerificationEnable(
        Request $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user->isTwoFaVerified()) {
            $this->addFlash('error', '2-Step Verification already enabled!');

            return $this->redirectToRoute('settings_profile');
        }

        if ($request->isMethod('POST') && $this->isCsrfTokenValid('enable_2fa', $request->request->get('token'))) {
            if (!$this->profileService->isTwoFaCodeValid($user, $request->request->get('code'))) {
                return $this->render('website/security/2fa_enable.html.twig', [
                    'error' => 'The verification code is not valid.',
                    'secret' => $user->getGoogleAuthenticatorSecret(),
                    'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user),
                    'reset_tokens' => $this->profileService->generateTwoStepVerificationOneTimeTokens($user, $request)
                ]);
            }

            $this->profileService->verifyTwoStepSecret($user);
            $this->profileService->saveTwoStepOneTimeTokens($user, $request);
            $this->addFlash('success', '2-Step Verification successfully enabled!');

            return $this->redirectToRoute('settings_profile');
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            $this->accountService->updateTwoStepSecret($user);
        }

        return $this->render('website/security/2fa_enable.html.twig', [
            'error' => null,
            'secret' => $user->getGoogleAuthenticatorSecret(),
            'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user),
            'reset_tokens' => $this->profileService->generateTwoStepVerificationOneTimeTokens($user, $request)
        ]);
    }


}
