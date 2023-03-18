<?php declare(strict_types=1);

namespace App\Controller\Website\Dashboard;

use App\Engine\Search\ItemSearchEngine;
use App\Entity\User;
use App\Repository\ItemRepository;
use App\Serializer\UserNormalizer;
use App\Service\PaginationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CreatorController extends AbstractController
{
    public function __construct(
        private readonly PaginationService $paginationService,
        private readonly ItemSearchEngine $itemSearchEngine,
        private readonly ItemRepository $itemRepository,
        private readonly UserNormalizer $userNormalizer
    )
    {

    }

    #[Route('/dashboard/creator', name: 'dashboard_creator')]
    public function showCreator(
        Request $request
    ): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $query = $request->query->all();

        $items = $this->paginationService->getDataByPage($this->itemSearchEngine->search($query, $user), $query);

        $user = $this->userNormalizer->normalize($user);

        return $this->render('website/dashboard/creator.html.twig', [
            'path_name' => 'creator',
            'items' => $items,
            'user' => $user,
            'amount' => $this->paginationService->getTotalAmount(),
            'current_page' => $this->paginationService->getCurrentPage(),
            'max_pages' => $this->paginationService->getTotalPages()
        ]);
    }

    #[Route('/dashboard/creator/create_item', name: 'creator_create_item')]
    public function createItem(): Response
    {

        return new Response();
    }

    #[Route('/dashboard/creator/create_group', name: 'creator_create_group')]
    public function createGroup(): Response
    {

        return new Response();
    }

    #[Route('/dashboard/creator/create_collection', name: 'creator_create_collection')]
    public function createCollection(): Response
    {

        return new Response();
    }

    #[Route('/dashboard/creator/item/{id}', name: 'creator_item_by_id', requirements: ['id' => '\d+'], methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function itemInfoSettings(
        Request $request,
        int $id
    ): Response
    {
        $user = $this->getUser();
        $item = $this->itemRepository->findOneBy(['user' => $user, 'id' => $id]);

        if (is_null($item)) {
            $this->addFlash('error', 'You cannot edit this item because you did not create it!');
            return $this->redirectToRoute('dashboard_creator');
        }

        return $this->render('website/dashboard/creator/infoAndSettingsForItem.html.twig', [
            'path_name' => 'creator'
        ]);
    }


}
