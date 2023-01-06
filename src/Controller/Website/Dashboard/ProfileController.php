<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Serializer\UserNormalizer;
use App\Repository\UserRepository;
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
        private readonly UserRepository $userRepository,
        private readonly Security $security
    )
    {
    }

    /**
     * @Route("/dashboard/profile", name="dashboard_profile")
     */
    public function showProfile(
        Request $request
    ): Response
    {
        $user = $this->security->getUser();

        if ($request->isMethod('POST')) {
            $this->profileService->updateProfile($user, $request);
        }

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }

    /**
     * @Route("/dashboard/profile/2-step-verification", name="dashboard_profile_two_factor_authentication", methods={"GET", "POST"})
     */
    public function twoStepVerificationAction(
        Request $request
    ): Response
    {
        $query = $request->query->all();

        if (!array_key_exists('action', $query)) {
            $this->addFlash('error', 'The URL was broken');

            return $this->redirectToRoute('dashboard_profile');
        }

        $user = $this->userRepository->findOneBy(['id' => $this->getUser()->getId()]);

        if (!$user->isTwoFaVerified() && !$user->isGoogleAuthenticatorEnabled() && $query['action'] === 'disable') {
            $this->addFlash('error', '2-Step Verification already disabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($user->isTwoFaVerified() && $query['action'] === 'enable') {
            $this->addFlash('error', '2-Step Verification already enabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        $code = $request->request->get('code');

        if ($request->isMethod('POST') && $query['action'] === 'disable') {
            if (!$this->profileService->isTwofaCodeValid($user, $code)) {
                $this->addFlash('error', 'The code is wrong!');

                return $this->render('website/security/2fa_desetup.html.twig', [
                    'action' => $this->redirectToRoute('dashboard_profile_two_factor_authentication')->getTargetUrl() . '?action=disable'
                ]);
            }

            $this->profileService->disableTwoStepVerification($user);
            $this->addFlash('success', '2-Step Verification successfully disabled!');

            return $this->redirectToRoute('dashboard_profile');
        }

        if ($request->isMethod('POST') && $query['action'] === 'enable') {
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
           return $this->render('website/security/2fa_desetup.html.twig', [
               'action' => $this->redirectToRoute('dashboard_profile_two_factor_authentication')->getTargetUrl() . '?action=disable'
           ]);
        }

        if (!$user->isGoogleAuthenticatorEnabled()) {
            $this->accountService->updateTwoStepSecret($user);
        }

        return $this->render('website/security/2fa_setup.html.twig', [
            'code' => $user->getGoogleAuthenticatorSecret(),
            'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user)
        ]);
    }


}
