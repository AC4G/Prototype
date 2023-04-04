<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class Documentation extends AbstractController
{
    #[Route('/documentation', name: 'documentation')]
    public function getDocumentation(): Response
    {
        return $this->render('website/documentation/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
