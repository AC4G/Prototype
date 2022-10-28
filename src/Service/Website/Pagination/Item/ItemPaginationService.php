<?php declare(strict_types=1);

namespace App\Service\Website\Pagination\Item;

use App\Repository\ItemRepository;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemPaginationService
{
    public function __construct(
        private ItemRepository $itemRepository
    )
    {
    }

    public function getDataByPage(
        int $limit,
        int $page,
        UserInterface $user
    ): array
    {
        $currentPage = $this->getCurrentPage($this->getMaxPages($limit, $user), $page);
        $offset = ($currentPage - 1) * $limit;

        return $this->itemRepository->findBy(['user' => $user], [], $limit, $offset);
    }

    private function getMaxPages(
        int $limit,
        UserInterface $user
    ): int
    {
        return (int)ceil(count($this->itemRepository->findBy(['user' => $user])) / $limit);
    }

    private function getCurrentPage(
        int $maxPages,
        int $page
    ): int
    {
        return min(max($page, 1), $maxPages);
    }
}