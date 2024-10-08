<?php

namespace App\Repository;

use App\Dto\SearchInput;

interface ReadEventRepository
{
    public function countAll(SearchInput $searchInput): int;

    /**
     * @return array{ pullRequest?: positive-int, commit?: positive-int, comment?: positive-int }
     */
    public function countByType(SearchInput $searchInput): array;

    /**
     * @return array<int<0, 23>, array{ commit: positive-int, pullRequest: positive-int, comment: positive-int }>
     */
    public function statsByTypePerHour(SearchInput $searchInput): array;

    /**
     * @return list<array{ type: string, repo: array<mixed> }>
     */
    public function getLatest(SearchInput $searchInput): array;

    public function exist(int $id): bool;
}
