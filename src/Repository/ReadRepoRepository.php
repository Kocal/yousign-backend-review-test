<?php

namespace App\Repository;

use App\Entity\Repo;

interface ReadRepoRepository
{
    public function find(int $id): ?Repo;
}
