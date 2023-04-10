<?php declare(strict_types=1);

namespace App\Controller\Website\Developer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ProjectController extends AbstractController
{
    #[Route('/developer/{organisation}/projects', name: 'developer_projects_by_organisation')]
    public function projectAction(
        Request $request,
        string $organisation
    ): Response
    {
        return $this->render('website/developer/project.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/developer/{organisation}/projects/create', name: 'developer_create_project_by_organisation', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function createProject(
        Request $request,
        string $organisation
    ): Response
    {
        return $this->render('website/developer/projectCreate.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
