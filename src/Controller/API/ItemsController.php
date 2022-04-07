<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Repository\ItemRepository;
use App\Service\DataService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class ItemsController extends AbstractController
{
    public function __construct(
        private ItemRepository $itemRepository,
        private DataService $dataService
    )
    {
    }

    /**
     * @Route("/api/items", name="api_items")
     */
    public function showItems(): Response
    {
        $data = $this->itemRepository->findAll();

        $json = $this->dataService->buildUnifiedDataCollection($data, [
            'parameter',
            'path',
        ], [
            'email',
            'password',
            'private',
            'emailVerified',
            'creationDate',
            'roles',
            'userIdentifier',
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        ], 'user', true);

        return new Response($json);
    }
}