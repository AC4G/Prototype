<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Website\Dashboard\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DashboardController extends AbstractController
{
    public function __construct(
        private DashboardService $dashboardService
    )
    {
    }

    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function showDashboard(): Response
    {
        return $this->render('website/dashboard/index.html.twig', $this->dashboardService->addDefaultParameter($this->getUser())->getParameter());
    }

    /**
     * @Route("/dashboard/account", name="dashboard_account")
     */
    public function showAccount(): Response
    {
        return $this->render('website/dashboard/account.html.twig', $this->dashboardService->addDefaultParameter($this->getUser())->getParameter());
    }

    /**
     * @Route("/dashboard/items", name="dashboard_items")
     */
    public function showItems(): Response
    {
        return $this->render('website/dashboard/items.html.twig', $this->dashboardService->addDefaultParameter($this->getUser())->getParameter());
    }

    /**
     * @Route("/dashboard/inventory", name="dashboard_inventory")
     */
    public function showInventory(): Response
    {
        return $this->render('website/dashboard/inventory.html.twig', $this->dashboardService->addDefaultParameter($this->getUser())->getParameter());
    }
}
