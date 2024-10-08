<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Repo;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DbalReadRepoRepository implements ReadRepoRepository
{
    /** @var EntityRepository<Repo> */
    private EntityRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Repo::class);
    }

    public function find(int $id): ?Repo
    {
        return $this->repository->find($id);
    }
}
