<?php

namespace App\Repository;

use App\Dto\EventInput;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;

class DbalWriteEventRepository implements WriteEventRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function update(EventInput $authorInput, int $id): void
    {
        $sql = <<<SQL
        UPDATE event
        SET comment = :comment
        WHERE id = :id
SQL;

        $this->entityManager->getConnection()->executeQuery($sql, ['id' => $id, 'comment' => $authorInput->comment]);
    }

    public function save(Event $event, bool $flush = false): void
    {
        $this->entityManager->persist($event);

        if ($flush) {
            $this->entityManager->flush();
        }
    }
}
