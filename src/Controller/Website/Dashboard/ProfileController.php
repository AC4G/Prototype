<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Serializer\UserNormalizer;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Dashboard\ProfileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProfileController extends AbstractController
{
    public function __construct(
        private UserNormalizer $userNormalizer,
        private ProfileService $profileService,
        private Security $security
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
            $this->profileService->updateEntries($user, $request);
        }

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }


}
