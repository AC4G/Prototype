<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\Security\Core\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class HomeController extends AbstractController
{
    public function __construct(
        private Security $security
    )
    {
    }

    /**
     * @Route("/", name="home")
     */
    public function showHomeTemplate(): Response
    {
        $user = [];

        if ($this->security->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser();
        }

        return $this->render('website/home/index.html.twig', [
            'user' => $user
        ]);
    }
}
