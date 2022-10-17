<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Repository\ItemRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class CreatorController extends AbstractController
{
    public function __construct(
        private ItemRepository $itemRepository
    )
    {

    }

    /**
     * @Route("/dashboard/creator/create_item", name="creator_create_item")
     */
    public function createItem(): Response
    {

        return new Response();
    }

    /**
     * @Route("/dashboard/creator/create_group", name="creator_create_group")
     */
    public function createGroup(): Response
    {

        return new Response();
    }

    /**
     * @Route("/dashboard/creator/create_collection", name="creator_create_collection")
     */
    public function createCollection(): Response
    {

        return new Response();
    }

    /**
     * @Route("/dashboard/creator/item/{id}", name="creator_item_by_id")
     */
    public function itemById(
        int $id
    ): Response
    {
        $user = $this->getUser();
        $item = $this->itemRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (is_null($item)) {
            return $this->render('error/website/permissionDenied.html.twig');
        }

        return $this->render('website/dashboard/creator/infoAndSettingsForItem.html.twig', [
            'path_name' => 'creator'
        ]);
    }
}