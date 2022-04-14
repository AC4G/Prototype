<?php declare(strict_types=1);

namespace App\Controller\API;

use App\Service\DataService;
use App\Repository\InventoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class InventoryController
{
    public function __construct(
        private InventoryRepository $inventoryRepository,
        private DataService $dataService
    )
    {
    }

    /**
     * @Route("/api/inventories", name="api_inventories", methods={"GET"})
     */
    public function showInventories(): Response
    {
        //TODO:only for admins ->authentication via jwt

        $data = $this->inventoryRepository->findAll();

        if (count($data) > 0) {
            $data = $this->dataService
                ->convertObjectToArray($data)
                ->rebuildPropertyArray('user', [
                    'id',
                    'nickname'
                ])
                ->rebuildPropertyArray('item', [
                    'id',
                    'name',
                    'gameName'
                ])
                ->convertPropertiesToJson([
                    'parameter'
                ])
                ->getArray();
        }

        return new JsonResponse(
            $data
        );
    }

    /**
     * @Route("/api/inventories/{property}", name="api_inventories_by_property", methods={"GET", "POST", "PATCH"})
     */
    public function processInventory(): Response
    {

        return new JsonResponse();
    }
}