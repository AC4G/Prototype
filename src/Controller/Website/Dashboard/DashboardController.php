<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Entity\User;
use App\Serializer\UserNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class DashboardController extends AbstractController
{
    public function __construct(
        private readonly UserNormalizer $userNormalizer
    )
    {
    }

    #[Route('/dashboard', name: 'dashboard')]
    public function showDashboard(
        Request $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user = $this->userNormalizer->normalize($user);

        return $this->render('website/dashboard/index.html.twig', [
            'user' => $user,
            'path_name' => 'dashboard'
        ]);
    }

    #[Route('/dashboard/items', name: 'dashboard_items')]
    public function showItems(): Response
    {
        return $this->render('website/dashboard/items.html.twig', [
            'path_name' => 'items'
        ]);
    }

    #[Route('/dashboard/inventory', name: 'dashboard_inventory')]
    public function showInventory(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user = $this->userNormalizer->normalize($user);

        return $this->render('website/dashboard/inventory.html.twig', [
            'user' => $user,
            'path_name' => 'inventory'
        ]);
    }

    #[Route('/dashboard/developer', name: 'dashboard_developer')]
    public function showDeveloper(): Response
    {
        return $this->render('website/dashboard/developer.html.twig', [
            'path_name' => 'developer'
        ]);
    }


}
