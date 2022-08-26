<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Serializer\UserNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Account\AccountService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DashboardController extends AbstractController
{
    public function __construct(
        private UserNormalizer $userNormalizer,
        private AccountService $accountService
    )
    {
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function showDashboard(): Response
    {
        return $this->render('website/dashboard/index.html.twig', [

        ]);
    }

    /**
     * @Route("/dashboard/profile", name="dashboard_profile")
     */
    public function showProfile(
        Request $request
    ): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if ($request->request->get('form-type') === 'picture') {
                $file = $request->files->get('profile-picture');
                $types = [
                    'image/gif',
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/jfif'
                ];

                if (is_null($file)) {
                    goto a;
                }

                if (!in_array($file->getMimeType(), $types)) {
                    goto a;
                }

                $this->accountService->saveProfilePicture($file, $user);
            }

            if ($request->request->get('form-type') === 'privacy') {
                $privacy = $request->request->get('privacy');

                if (is_null($privacy)) {
                    $privacy = true;
                }

                if ($privacy === 'on') {
                    $privacy = false;
                }

                $this->accountService->updateProfilePrivacy($privacy, $user);
            }


            if ($request->request->get('form-type') === 'nickname') {
                $nickname = $request->request->get('nickname');

                if (is_null($nickname)) {
                    goto a;
                }

                $this->accountService->updateNickname($nickname, $user);
            }

            if ($request->request->get('form-type') === 'email') {
                $email = $request->request->get('email');

                if (is_null($email)) {
                    goto a;
                }

                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    goto a;
                }

                $this->accountService->updateEmail($email, $user);
            }
        }

        a:

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user
        ]);
    }


    /**
     * @Route("/dashboard/items", name="dashboard_items")
     */
    public function showItems(): Response
    {
        return $this->render('website/dashboard/items.html.twig', [

        ]);
    }

    /**
     * @Route("/dashboard/inventory", name="dashboard_inventory")
     */
    public function showInventory(): Response
    {
        return $this->render('website/dashboard/inventory.html.twig', [

        ]);
    }

    /**
     * @Route("/dashboard/creator", name="dashboard_creator")
     */
    public function showCreator(): Response
    {
        return $this->render('website/dashboard/creator.html.twig', [

        ]);
    }

    /**
     * @Route("/dashboard/developer", name="dashboard_developer")
     */
    public function showDeveloper(): Response
    {
        return $this->render('website/dashboard/developer.html.twig', [

        ]);
    }


}
