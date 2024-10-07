<?php
declare(strict_types=1);

namespace App\Message;

final class ImportGitHubEventsForDate
{
    public function __construct(
        private readonly \DateTimeInterface $date,
    ) {
    }
    
    public function getDate(): \DateTimeInterface
    {
        return $this->date;
    }
}
