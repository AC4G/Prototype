<?php declare(strict_types=1);

namespace App\Controller\Website;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CreatorController extends AbstractController
{
    public function __construct(

    )
    {
    }

    #[Route('/creator', name: 'creator')]
    public function showCreator(
        Request $request
    ): Response
    {
        return $this->render('website/creator/index.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/creator/createItem', name: 'creator_create_item')]
    public function createItem(): Response
    {

        return $this->render('website/creator/createItem.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/creator/createGroup', name: 'creator_create_group')]
    public function createGroup(): Response
    {

        return $this->render('website/creator/createGroup.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/creator/createCollection', name: 'creator_create_collection')]
    public function createCollection(): Response
    {

        return $this->render('website/creator/createCollection.html.twig', [
            'user' => $this->getUser()
        ]);
    }

    #[Route('/creator/item/{id}', name: 'creator_item_by_id', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function itemInfoSettings(
        Request $request,
        int $id
    ): Response
    {

        return $this->render('website/creator/item.html.twig', [
            'user' => $this->getUser()
        ]);
    }


}
