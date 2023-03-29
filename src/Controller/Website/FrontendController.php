<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class FrontendController extends AbstractController
{
    #[Route('/{path}', defaults: ['path' => ''])]
    public function index(): Response
    {
        return $this->render('react.base.html.twig');
    }


}
