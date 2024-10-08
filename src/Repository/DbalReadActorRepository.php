<?php
declare(strict_types=1);

namespace App\Repository;

use App\Entity\Actor;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class DbalReadActorRepository implements ReadActorRepository
{
    /** @var EntityRepository<Actor> */
    private EntityRepository $repository;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->repository = $entityManager->getRepository(Actor::class);
    }

    public function find(int $id): ?Actor
    {
        return $this->repository->find($id);
    }
}
