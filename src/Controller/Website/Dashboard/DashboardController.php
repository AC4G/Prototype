<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Repository\ItemRepository;
use App\Serializer\UserNormalizer;
use App\Service\API\Items\ItemsService;
use App\Engine\Search\ItemSearchEngine;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Account\AccountService;
use App\Service\Website\Dashboard\DashboardService;
use App\Service\Website\Pagination\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DashboardController extends AbstractController
{
    public function __construct(
        private PaginationService $paginationService,
        private DashboardService  $dashboardService,
        private ItemSearchEngine  $itemSearchEngine,
        private ItemRepository    $itemRepository,
        private UserNormalizer    $userNormalizer,
        private AccountService    $accountService,
        private ItemsService      $itemsService,
        private Security          $security
    )
    {
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function showDashboard(): Response
    {
        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/index.html.twig', [
            'user' => $user,
            'path_name' => 'dashboard'
        ]);
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
            if ($request->request->get('form-type') === 'picture') {
                $file = $request->files->get('profile-picture');
                $types = [
                    'image/gif',
                    'image/jpeg',
                    'image/jpg',
                    'image/png',
                    'image/jfif'
                ];

                if (is_null($file) || !in_array($file->getMimeType(), $types)) {
                    goto a;
                }

                $this->accountService->saveProfilePicture($file, $user);

                sleep(2);
            }

            if ($request->request->get('form-type') === 'privacy') {
                $privacy = $request->request->get('privacy');

                if ($privacy === '1') {
                    $privacy = true;
                }

                if ($privacy === '0') {
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

                if (is_null($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    goto a;
                }

                $this->accountService->updateEmail($email, $user);
            }

            if ($request->request->get('form-type') === 'password') {
                $password1 = $request->request->get('password-1');
                $password2 = $request->request->get('password-2');

                if ((is_null($password1) || is_null($password2)) || $password1 !== $password2 || strlen($password2) < 10) {
                    goto a;
                }

                $this->accountService->updatePassword($password2, $user);
            }
        }

        a:

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/profile.html.twig', [
            'user' => $user,
            'path_name' => 'profile'
        ]);
    }


    /**
     * @Route("/dashboard/items", name="dashboard_items")
     */
    public function showItems(): Response
    {
        return $this->render('website/dashboard/items.html.twig', [
            'path_name' => 'items'
        ]);
    }

    /**
     * @Route("/dashboard/inventory", name="dashboard_inventory")
     */
    public function showInventory(): Response
    {
        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/inventory.html.twig', [
            'user' => $user,
            'path_name' => 'inventory'
        ]);
    }

    /**
     * @Route("/dashboard/creator", name="dashboard_creator")
     */
    public function showCreator(
        Request $request
    ): Response
    {
        $query = $request->query->all();

        $page = array_key_exists('page', $query) ? (int)$query['page'] : 1;
        $limit = array_key_exists('limit', $query) ? (int)$query['limit'] : 1;
        $phrase = array_key_exists('search', $query) ? $query['search'] : null;

        $user = $this->security->getUser();

        $items = $this->itemsService->prepareData($this->paginationService->getDataByPage($this->itemSearchEngine->search($phrase, $user), $limit, $page));

        $user = $this->userNormalizer->normalize($this->getUser());

        return $this->render('website/dashboard/creator.html.twig', [
            'path_name' => 'creator',
            'items' => $items,
            'user' => $user,
            'amount' => $this->paginationService->getAmount(),
            'current_page' => $this->paginationService->currentPage(),
            'max_pages' => $this->paginationService->maxPages()
        ]);
    }

    /**
     * @Route("/dashboard/developer", name="dashboard_developer")
     */
    public function showDeveloper(): Response
    {
        return $this->render('website/dashboard/developer.html.twig', [
            'path_name' => 'developer'
        ]);
    }


}
