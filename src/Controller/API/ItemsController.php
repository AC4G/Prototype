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

        $json = $this->dataService->convertObjectToArray($data)->rebuildPropertyArray('user', [
            'nickname',
        ])->removeProperties([
            'path',
        ])->convertPropertiesToJson([
            'parameter',
        ])->getJson();

        return new Response($json);
    }
}