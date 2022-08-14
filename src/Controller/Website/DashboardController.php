<?php declare(strict_types=1);

namespace App\Controller\Website;

use App\Serializer\UserNormalizer;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DashboardController extends AbstractController
{
    public function __construct(
        private UserNormalizer $userNormalizer,
        private UserRepository $userRepository
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
     * @Route("/dashboard/account", name="dashboard_account")
     */
    public function showAccount(
        Request $request
    ): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            if ($request->request->get('form-type') === 'picture') {
                $file = $request->files->get('profile-picture');

                if (is_null($file)) {
                    return $this->redirectToRoute('dashboard_account');
                }

                $extension = substr($file->getClientOriginalName(), strpos($file->getClientOriginalName(), '.') + 1);

                if (is_null($user)) {
                    return $this->redirectToRoute('home');
                }

                $nickname = strtolower($user->getNickname());
                $dir = 'files/profile/' . $nickname . '/';

                if (file_exists($dir) && count(scandir($dir)) > 0) {
                    $files = glob($dir . '*', GLOB_MARK);
                    foreach ($files as $fileOld) {
                        if (is_file($fileOld)) {
                            unlink($fileOld);
                        }
                    }
                }
                if (!file_exists($dir)) {
                    mkdir('files/profile/' . $nickname);
                }

                $file->move($dir, $nickname . '.' . $extension);

                $user->setProfilePic($dir . $nickname . '.' . $extension);
                $this->userRepository->flushEntity();

                sleep(1);
            }
        }

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/account.html.twig', [
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
