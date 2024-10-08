<?php
declare(strict_types=1);

namespace App\Tests\Toolbox\EventsFetcher;

use App\EventsFetcher\EventsFetcher;

final class ArrayJsonEventsFetcher implements EventsFetcher
{
    public function __construct(private array $events)
    {
    }

    public function fetchForDate(\DateTimeInterface $date): iterable
    {
        foreach ($this->events as $event) {
            $decoded = json_decode($event, associative: true, flags: JSON_THROW_ON_ERROR);
            $createdAt = new \DateTimeImmutable($decoded['created_at'] ?? throw new \LogicException('Missing "created_at" key in event.'));
            
            if ($date->diff($createdAt)->days === 0) {
                yield $decoded;
            }
        }
    }
}
