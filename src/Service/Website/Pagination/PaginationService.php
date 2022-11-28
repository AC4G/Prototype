<?php declare(strict_types=1);

namespace App\Service\Website\Pagination;

final class PaginationService
{
    private int $maxPages = 1;
    private int $currentPage = 1;
    private int $amount = 0;

    public function getDataByPage(
        array $content,
        array $query
    ): array
    {
        $limit = array_key_exists('limit', $query) ? (int)$query['limit'] : 20;
        $page = array_key_exists('page', $query) ? (int)$query['page'] : 1;

        $currentPage = $this->decideForCurrentPage($this->calculateMaxPages($content, $limit), $page);
        $this->amount = count($content);

        if ($currentPage === 0) {
            return [];
        }

        $offset = $this->calculateOffset($currentPage, $limit);

        return array_slice($content, $offset, $limit);
    }

    private function calculateOffset(
        int $currentPage,
        int $limit
    ): int{
        return ($currentPage - 1) * $limit;
    }

    private function calculateMaxPages(
        array $content,
        int $limit
    ): int
    {
        $max = (int)ceil(count($content) / $limit);

        if ($max === 0) {
            $max = 1;
        }

        return $this->maxPages = $max;
    }

    private function decideForCurrentPage(
        int $maxPages,
        int $page
    ): int
    {
        return $this->currentPage = min(max($page, 1), $maxPages);
    }

    public function getMaxPages():int
    {
        return $this->maxPages;
    }

    public function getCurrentPage():int
    {
        return $this->currentPage;
    }

    public function getAmountOfItems(): int
    {
        return $this->amount;
    }


}
