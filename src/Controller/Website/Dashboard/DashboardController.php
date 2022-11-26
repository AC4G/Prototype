<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Serializer\UserNormalizer;
use App\Engine\Search\ItemSearchEngine;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Dashboard\DashboardService;
use App\Service\Website\Pagination\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DashboardController extends AbstractController
{
    public function __construct(
        private PaginationService $paginationService,
        private DashboardService $dashboardService,
        private ItemSearchEngine $itemSearchEngine,
        private UserNormalizer $userNormalizer,
        private Security $security
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

        $user = $this->security->getUser();

        $items = $this->paginationService->getDataByPage($this->itemSearchEngine->search(
            array_key_exists('search', $query) ? $query['search'] : null, $user
            ),
            array_key_exists('limit', $query) ? (int)$query['limit'] : 20,
            array_key_exists('page', $query) ? (int)$query['page'] : 1
        );

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
