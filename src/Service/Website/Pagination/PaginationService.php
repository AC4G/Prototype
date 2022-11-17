<?php declare(strict_types=1);

namespace App\Service\Website\Pagination;

final class PaginationService
{
    private int $maxPages = 1;
    private int $currentPage = 1;

    public function __construct(

    )
    {
    }

    public function getDataByPage(
        array $content,
        int $limit,
        int $page
    ): array
    {
        $currentPage = $this->getCurrentPage($this->getMaxPages($content, $limit), $page);

        if ($currentPage === 0) {
            return [];
        }

        $offset = ($currentPage - 1) * $limit;

        return array_slice($content, $offset, $limit);
    }

    private function getMaxPages(
        array $content,
        int $limit
    ): int
    {
        return $this->maxPages = (int)ceil(count($content) / $limit);
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
