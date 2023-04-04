<?php declare(strict_types=1);

namespace App\Controller\Website\Developer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProjectController extends AbstractController
{
    #[Route('/developer/project', name: 'developer_project')]
    public function projectAction(): Response
    {
        return $this->render('website/developer/project.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/developer/project/create', name: 'developer_project_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function createProject(): Response
    {
        return $this->render('website/developer/projectCreate.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
