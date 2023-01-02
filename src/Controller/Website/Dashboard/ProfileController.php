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
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

            $this->profileService->updateProfile($user, $request);
        }

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }

    /**
     * @Route("/dashboard/profile/2-step-verification", name="dashboard_profile_two_factor_authentication")
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

        if ($query['action'] === 'enable') {
            $this->accountService->updateTwoStepSecret($user);

            return $this->render('website/security/2fa_QR_code.html.twig', [
                'qrCode' => $this->profileService->generateTwoStepVerificationQRCode($user)
            ]);
        }

        return $this->render('website/security/2fa_QR_code.html.twig', [

        ]);
    }


}
