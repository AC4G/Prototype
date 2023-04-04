<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class InventoryController extends AbstractController
{
    #[Route('/inventory', name: 'inventory')]
    public function index(): Response
    {

        return $this->render('website/inventory/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
