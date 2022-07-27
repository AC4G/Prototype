<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function showHomeTemplate(): Response
    {
        return $this->render('website/home/index.html.twig', [

        ]);
    }
}
