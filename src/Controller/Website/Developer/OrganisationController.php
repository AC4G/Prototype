<?php declare(strict_types=1);

namespace App\Controller\Website\Developer;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrganisationController extends AbstractController
{
    #[Route('/developer/organisation/create', name: 'developer_organisation_create', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function createOrganisation(
        Request $request
    ): Response
    {
        return new Response();
    }


}
