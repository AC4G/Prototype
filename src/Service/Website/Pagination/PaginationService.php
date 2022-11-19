<?php declare(strict_types=1);

namespace App\Service\Website\Pagination;

final class PaginationService
{
    private int $maxPages = 1;
    private int $currentPage = 1;
    private int $amount = 0;

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
        $this->amount = count($content);

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
        $max = (int)ceil(count($content) / $limit);

        if ($max === 0) {
            $max = 1;
        }

        return $this->maxPages = $max;
    }

    private function getCurrentPage(
        int $maxPages,
        int $page
    ): int
    {
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

    public function getAmount(): int
    {
        return $this->amount;
    }


}
