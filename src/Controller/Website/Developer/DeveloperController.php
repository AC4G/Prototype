<?php declare(strict_types=1);

namespace App\Controller\Website\Developer;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class DeveloperController extends AbstractController
{
    #[Route('/developer', name: 'developer')]
    public function developerAction():Response
    {
        return $this->render('website/developer/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
