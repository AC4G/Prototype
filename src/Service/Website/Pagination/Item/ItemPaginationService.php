<?php declare(strict_types=1);

namespace App\Service\Website\Pagination\Item;

use App\Repository\ItemRepository;
use Symfony\Component\Security\Core\User\UserInterface;

final class ItemPaginationService
{
    private int $maxPages = 1;
    private int $currentPage = 1;

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

        if ($currentPage === 0) {
            return [];
        }

        $offset = ($currentPage - 1) * $limit;

        return $this->itemRepository->findBy(['user' => $user], [], $limit, $offset);
    }

    private function getMaxPages(
        int $limit,
        UserInterface $user
    ): int
    {
        return $this->maxPages = (int)ceil(count($this->itemRepository->findBy(['user' => $user])) / $limit);
    }

    private function getCurrentPage(
        int $maxPages,
        int $page
    ): int
    {
        if ($maxPages === 0) {
            return 0;
        }

        return $this->currentPage = min(max($page, 1), $maxPages);
    }

    public function maxPages():int
    {
        return $this->maxPages;
    }

    public function currentPage():int
    {
        return $this->currentPage;
    }


}
